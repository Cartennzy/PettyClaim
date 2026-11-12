import 'dart:async';
import 'dart:math' as math;
import 'package:flutter/material.dart';
import '../auth/login_screen.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen>
    with TickerProviderStateMixin {
  late AnimationController _masterController;
  late AnimationController _rotateController;
  late AnimationController _particleController;

  // Animasi Fase Utama
  late Animation<double> _circleProgress;
  late Animation<double> _logoScale;
  late Animation<double> _logoOpacity;
  late Animation<double> _shockwaveRadius;
  late Animation<double> _shockwaveOpacity;
  late Animation<Offset> _logoSlideLeft;
  late Animation<double> _textWidthFactor;
  late Animation<double> _textOpacity;

  Timer? _navigationTimer;
  final List<_ParticleModel> _particles = [];

  @override
  void initState() {
    super.initState();

    // 1. Controller Animasi Sekuensial
    _masterController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 3000),
    );

    // 2. Controller Rotasi Halo Ring
    _rotateController = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 10),
    )..repeat();

    // 3. Controller Pergerakan Partikel Latar
    _particleController = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 10),
    )..repeat();

    // Inisialisasi Partikel
    final random = math.Random();
    for (int i = 0; i < 35; i++) {
      _particles.add(
        _ParticleModel(
          x: random.nextDouble(),
          y: random.nextDouble(),
          size: random.nextDouble() * 2.5 + 1.0,
          speedX: (random.nextDouble() - 0.5) * 0.002,
          speedY: (random.nextDouble() - 0.5) * 0.002,
          opacity: random.nextDouble() * 0.5 + 0.2,
          isSpark: random.nextBool(),
        ),
      );
    }

    // Phase 1: Logo Scale Pop & Fade In
    _logoOpacity = CurvedAnimation(
      parent: _masterController,
      curve: const Interval(0.0, 0.30, curve: Curves.easeIn),
    );

    _logoScale = Tween<double>(begin: 0.4, end: 1.0).animate(
      CurvedAnimation(
        parent: _masterController,
        curve: const Interval(0.0, 0.38, curve: Curves.easeOutBack),
      ),
    );

    // Phase 1.5: Circle Tracer Progress
    _circleProgress = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _masterController,
        curve: const Interval(0.05, 0.45, curve: Curves.easeInOutCubic),
      ),
    );

    // Phase 2: Circular Ripple Expansion
    _shockwaveRadius = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _masterController,
        curve: const Interval(0.25, 0.58, curve: Curves.easeOutCubic),
      ),
    );

    _shockwaveOpacity = Tween<double>(begin: 0.8, end: 0.0).animate(
      CurvedAnimation(
        parent: _masterController,
        curve: const Interval(0.30, 0.62, curve: Curves.easeOut),
      ),
    );

    // Phase 3: Pergeseran Logo ke Kiri
    _logoSlideLeft = Tween<Offset>(
      begin: Offset.zero,
      end: const Offset(-0.16, 0.0),
    ).animate(
      CurvedAnimation(
        parent: _masterController,
        curve: const Interval(0.48, 0.72, curve: Curves.easeInOutCubic),
      ),
    );

    // Phase 4: Text Unclip Reveal
    _textWidthFactor = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _masterController,
        curve: const Interval(0.65, 0.90, curve: Curves.easeOutCubic),
      ),
    );

    _textOpacity = CurvedAnimation(
      parent: _masterController,
      curve: const Interval(0.68, 0.92, curve: Curves.easeIn),
    );

    // Mulai animasi sekuensial
    _masterController.forward();

    // Navigasi Otomatis yang Aman ke LoginScreen
    _navigationTimer = Timer(const Duration(milliseconds: 3500), () {
      if (mounted) {
        Navigator.of(context).pushReplacement(
          PageRouteBuilder(
            transitionDuration: const Duration(milliseconds: 800),
            pageBuilder: (context, animation, secondaryAnimation) =>
                const LoginScreen(),
            transitionsBuilder:
                (context, animation, secondaryAnimation, child) {
              return FadeTransition(
                opacity: animation,
                child: child,
              );
            },
          ),
        );
      }
    });
  }

  @override
  void dispose() {
    _navigationTimer?.cancel();
    _masterController.dispose();
    _rotateController.dispose();
    _particleController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final screenSize = MediaQuery.of(context).size;
    final maxRippleRadius = screenSize.longestSide * 1.2;

    return Scaffold(
      backgroundColor:
          isDark ? const Color(0xFF020617) : const Color(0xFFF8FAFC),
      body: Stack(
        alignment: Alignment.center,
        children: [
          // 1. INTERACTIVE PARTICLE CANVAS LAYER
          AnimatedBuilder(
            animation: _particleController,
            builder: (context, child) {
              return CustomPaint(
                size: screenSize,
                painter: _ParticlePainter(_particles),
              );
            },
          ),

          // 2. EXPANDING CIRCULAR RIPPLE WAVE
          AnimatedBuilder(
            animation: _masterController,
            builder: (context, child) {
              if (_shockwaveRadius.value <= 0) return const SizedBox.shrink();
              return Container(
                width: _shockwaveRadius.value * maxRippleRadius,
                height: _shockwaveRadius.value * maxRippleRadius,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  gradient: RadialGradient(
                    colors: [
                      (isDark ? const Color(0xFF38BDF8) : const Color(0xFF0284C7))
                          .withValues(
                        alpha: 0.35 * _shockwaveOpacity.value,
                      ),
                      (isDark ? const Color(0xFF2563EB) : const Color(0xFF3B82F6))
                          .withValues(
                        alpha: 0.15 * _shockwaveOpacity.value,
                      ),
                      Colors.transparent,
                    ],
                    stops: const [0.2, 0.6, 1.0],
                  ),
                ),
              );
            },
          ),

          // 3. CENTER ASSEMBLY (LOGO + ROTATING HALO + TEXT REVEAL)
          Center(
            child: AnimatedBuilder(
              animation:
                  Listenable.merge([_masterController, _rotateController]),
              builder: (context, child) {
                return Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    // LOGO DENGAN ANIMATED CIRCLE TRACER
                    SlideTransition(
                      position: _logoSlideLeft,
                      child: SizedBox(
                        width: 140,
                        height: 140,
                        child: Stack(
                          alignment: Alignment.center,
                          children: [
                            CustomPaint(
                              size: const Size(140, 140),
                              painter: _CircleTracerPainter(
                                progress: _circleProgress.value,
                              ),
                            ),
                            FadeTransition(
                              opacity: _logoOpacity,
                              child: ScaleTransition(
                                scale: _logoScale,
                                child: RotationTransition(
                                  turns: _rotateController,
                                  child: Container(
                                    padding: const EdgeInsets.all(3.5),
                                    decoration: BoxDecoration(
                                      shape: BoxShape.circle,
                                      gradient: const SweepGradient(
                                        colors: [
                                          Color(0xFF38BDF8),
                                          Color(0xFF2563EB),
                                          Color(0xFF0284C7),
                                          Color(0xFF38BDF8),
                                        ],
                                      ),
                                      boxShadow: [
                                        BoxShadow(
                                          color: (isDark
                                                  ? const Color(0xFF38BDF8)
                                                  : const Color(0xFF0284C7))
                                              .withValues(
                                                  alpha: isDark ? 0.45 : 0.3),
                                          blurRadius: 32,
                                          spreadRadius: 2,
                                        ),
                                      ],
                                    ),
                                    child: Container(
                                      padding: const EdgeInsets.all(16),
                                      decoration: const BoxDecoration(
                                        shape: BoxShape.circle,
                                        // BACKGROUND PUTIH SOLID DI DALAM LOGO
                                        color: Colors.white,
                                      ),
                                      child: ClipOval(
                                        child: Image.asset(
                                          'assets/logo_pettyclaim.png',
                                          width: 85,
                                          height: 85,
                                          fit: BoxFit.contain,
                                          errorBuilder:
                                              (context, error, stackTrace) {
                                            return const Icon(
                                              Icons.account_balance_wallet_rounded,
                                              size: 75,
                                              color: Color(0xFF0284C7),
                                            );
                                          },
                                        ),
                                      ),
                                    ),
                                  ),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),

                    // BRAND TYPOGRAPHY UNCLIP REVEAL ANIMATION
                    ClipRect(
                      child: Align(
                        alignment: Alignment.centerLeft,
                        widthFactor: _textWidthFactor.value,
                        child: FadeTransition(
                          opacity: _textOpacity,
                          child: Padding(
                            padding: const EdgeInsets.only(left: 16.0),
                            child: Column(
                              mainAxisSize: MainAxisSize.min,
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                ShaderMask(
                                  shaderCallback: (bounds) =>
                                      LinearGradient(
                                    colors: isDark
                                        ? const [
                                            Colors.white,
                                            Color(0xFFBAE6FD),
                                            Color(0xFF38BDF8),
                                          ]
                                        : const [
                                            Color(0xFF0F172A),
                                            Color(0xFF0369A1),
                                            Color(0xFF0284C7),
                                          ],
                                  ).createShader(bounds),
                                  child: const Text(
                                    'PettyClaim',
                                    style: TextStyle(
                                      fontSize: 36,
                                      fontWeight: FontWeight.w900,
                                      color: Colors.white,
                                      letterSpacing: 1.2,
                                      height: 1.1,
                                    ),
                                  ),
                                ),
                                const SizedBox(height: 6),
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 10,
                                    vertical: 4,
                                  ),
                                  decoration: BoxDecoration(
                                    color: (isDark
                                            ? const Color(0xFF38BDF8)
                                            : const Color(0xFF0284C7))
                                        .withValues(alpha: 0.12),
                                    borderRadius: BorderRadius.circular(8),
                                    border: Border.all(
                                      color: (isDark
                                              ? const Color(0xFF38BDF8)
                                              : const Color(0xFF0284C7))
                                          .withValues(alpha: 0.25),
                                    ),
                                  ),
                                  child: Text(
                                    'FINANCE SYSTEM',
                                    style: TextStyle(
                                      fontSize: 10,
                                      color: isDark
                                          ? const Color(0xFF38BDF8)
                                          : const Color(0xFF0284C7),
                                      fontWeight: FontWeight.w800,
                                      letterSpacing: 1.2,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ),
                  ],
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}

// Custom Painter untuk Gambar Garis Lingkaran Animasi
class _CircleTracerPainter extends CustomPainter {
  final double progress;

  _CircleTracerPainter({required this.progress});

  @override
  void paint(Canvas canvas, Size size) {
    if (progress <= 0) return;

    final center = Offset(size.width / 2, size.height / 2);
    final radius = (size.width / 2) - 4;

    final glowPaint = Paint()
      ..color = const Color(0xFF38BDF8).withValues(alpha: 0.5)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 5.0
      ..maskFilter = const MaskFilter.blur(BlurStyle.normal, 6.0)
      ..strokeCap = StrokeCap.round;

    final strokePaint = Paint()
      ..shader = const SweepGradient(
        colors: [
          Color(0xFF38BDF8),
          Color(0xFF2563EB),
          Colors.white,
          Color(0xFF38BDF8),
        ],
      ).createShader(Rect.fromCircle(center: center, radius: radius))
      ..style = PaintingStyle.stroke
      ..strokeWidth = 3.5
      ..strokeCap = StrokeCap.round;

    final sweepAngle = 2 * math.pi * progress;
    const double startAngle = -math.pi / 2;

    canvas.drawArc(
      Rect.fromCircle(center: center, radius: radius),
      startAngle,
      sweepAngle,
      false,
      glowPaint,
    );

    canvas.drawArc(
      Rect.fromCircle(center: center, radius: radius),
      startAngle,
      sweepAngle,
      false,
      strokePaint,
    );
  }

  @override
  bool shouldRepaint(covariant _CircleTracerPainter oldDelegate) =>
      oldDelegate.progress != progress;
}

// Model Partikel Latar Belakang & Spark
class _ParticleModel {
  double x;
  double y;
  double size;
  double speedX;
  double speedY;
  double opacity;
  bool isSpark;

  _ParticleModel({
    required this.x,
    required this.y,
    required this.size,
    required this.speedX,
    required this.speedY,
    required this.opacity,
    required this.isSpark,
  });

  void update() {
    x += speedX;
    y += speedY;
    if (x < 0 || x > 1.0) speedX *= -1;
    if (y < 0 || y > 1.0) speedY *= -1;
  }
}

// Custom Painter Partikel Latar Belakang
class _ParticlePainter extends CustomPainter {
  final List<_ParticleModel> particles;

  _ParticlePainter(this.particles);

  @override
  void paint(Canvas canvas, Size size) {
    for (var p in particles) {
      p.update();
      final paint = Paint()
        ..color = (p.isSpark ? const Color(0xFFF59E0B) : const Color(0xFF38BDF8))
            .withValues(alpha: p.opacity)
        ..style = PaintingStyle.fill;

      final offset = Offset(p.x * size.width, p.y * size.height);
      canvas.drawCircle(offset, p.size, paint);
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => true;
}