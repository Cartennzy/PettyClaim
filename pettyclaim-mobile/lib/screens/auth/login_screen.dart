import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import '../../main.dart';
import '../../services/api_service.dart';
import '../karyawan/karyawan_dashboard_screen.dart';
import '../manager/manager_dashboard_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen>
    with SingleTickerProviderStateMixin {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();

  late AnimationController _btnScaleController;
  late Animation<double> _btnScaleAnimation;

  bool _isLoading = false;
  bool _obscurePassword = true;
  bool _rememberMe = true;

  String? _emailServerError;
  String? _passwordServerError;

  @override
  void initState() {
    super.initState();

    _btnScaleController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 120),
    );
    _btnScaleAnimation = Tween<double>(begin: 1.0, end: 0.96).animate(
      CurvedAnimation(
        parent: _btnScaleController,
        curve: Curves.easeInOutCubic,
      ),
    );
  }

  @override
  void dispose() {
    _btnScaleController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _handleLogin() async {
    setState(() {
      _emailServerError = null;
      _passwordServerError = null;
    });

    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);

    final email = _emailController.text.trim().toLowerCase();
    final password = _passwordController.text.trim();

    try {
      final response = await http.post(
        Uri.parse('${ApiService.baseUrl}/login'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'email': email,
          'password': password,
        }),
      );

      final data = jsonDecode(response.body);

      if (!mounted) return;
      setState(() => _isLoading = false);

      if (response.statusCode == 200 && data['success'] == true) {
        final userData = data['user'];
        final String role =
            (userData['role'] ?? 'karyawan').toString().toLowerCase();

        if (!mounted) return;

        _showTopNotification(
          context,
          message: 'Selamat datang kembali, ${userData['name']}!',
          isSuccess: true,
        );

        if (role == 'manager' || role == 'admin' || role == 'hrd') {
          Navigator.of(context).pushReplacement(
            MaterialPageRoute(
              builder: (context) =>
                  ManagerDashboardScreen(userEmail: userData['email']),
            ),
          );
        } else {
          Navigator.of(context).pushReplacement(
            MaterialPageRoute(
              builder: (context) =>
                  KaryawanDashboardScreen(userEmail: userData['email']),
            ),
          );
        }
      } else {
        final message = data['message'] ?? 'Terjadi kesalahan autentikasi.';
        final errors = data['errors'];

        setState(() {
          if (errors != null && errors is Map) {
            if (errors.containsKey('email')) {
              final errList = errors['email'];
              _emailServerError =
                  errList is List ? errList.first.toString() : errList.toString();
            }
            if (errors.containsKey('password')) {
              final errList = errors['password'];
              _passwordServerError =
                  errList is List ? errList.first.toString() : errList.toString();
            }
          } else {
            if (message.toLowerCase().contains('email') ||
                message.toLowerCase().contains('kredensial')) {
              _emailServerError = message;
            }
            _passwordServerError = message;
          }
        });

        _formKey.currentState!.validate();
      }
    } catch (e) {
      if (!mounted) return;
      setState(() => _isLoading = false);
      _showTopNotification(
        context,
        message: 'Gagal terhubung ke server backend.',
        isSuccess: false,
      );
    }
  }

  void _showTopNotification(BuildContext ctx,
      {required String message, required bool isSuccess}) {
    OverlayState? overlayState = Overlay.maybeOf(ctx);
    if (overlayState == null) return;

    late OverlayEntry overlayEntry;

    overlayEntry = OverlayEntry(
      builder: (context) {
        return _TopToastWidget(
          message: message,
          isSuccess: isSuccess,
          onDismiss: () {
            overlayEntry.remove();
          },
        );
      },
    );

    overlayState.insert(overlayEntry);
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final screenSize = MediaQuery.of(context).size;
    final isDesktop = screenSize.width > 768;

    final bgColor = isDark ? const Color(0xFF090F1D) : const Color(0xFFEAF0F9);
    final textDark = isDark ? Colors.white : const Color(0xFF1E293B);
    final textMuted = isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B);
    final inputBg = isDark ? const Color(0xFF131E33) : const Color(0xFFF3F6FD);
    final inputBorder = isDark ? const Color(0xFF24324F) : const Color(0xFFE2EAF8);

    return Scaffold(
      backgroundColor: bgColor,
      body: Stack(
        children: [
          // 1. BACKGROUND CLAY RINGS & GLOW AMBIENT
          Positioned(
            top: -70,
            left: -80,
            child: Container(
              width: 280,
              height: 280,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                border: Border.all(
                  color: const Color(0xFF38BDF8).withValues(alpha: isDark ? 0.18 : 0.4),
                  width: 32,
                ),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF0284C7).withValues(alpha: isDark ? 0.25 : 0.15),
                    blurRadius: 40,
                    spreadRadius: 10,
                  ),
                ],
              ),
            ),
          ),
          Positioned(
            top: 140,
            right: -70,
            child: Container(
              width: 220,
              height: 220,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                gradient: RadialGradient(
                  colors: [
                    const Color(0xFF38BDF8).withValues(alpha: isDark ? 0.15 : 0.25),
                    Colors.transparent,
                  ],
                ),
              ),
            ),
          ),
          Positioned(
            bottom: -90,
            left: -40,
            child: Container(
              width: 320,
              height: 320,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                border: Border.all(
                  color: const Color(0xFF0284C7).withValues(alpha: isDark ? 0.12 : 0.3),
                  width: 36,
                ),
              ),
            ),
          ),

          // 2. THEME MODE TOGGLE BUTTON (TOP RIGHT)
          Positioned(
            top: 48,
            right: 24,
            child: InkWell(
              onTap: () {
                themeModeNotifier.value =
                    isDark ? ThemeMode.light : ThemeMode.dark;
              },
              borderRadius: BorderRadius.circular(18),
              child: Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: isDark ? const Color(0xFF131D31) : const Color(0xFFF1F5FB),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(
                    color: isDark ? Colors.white.withValues(alpha: 0.08) : Colors.white,
                    width: 1.5,
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: isDark
                          ? Colors.black.withValues(alpha: 0.4)
                          : const Color(0xFF94A3B8).withValues(alpha: 0.22),
                      blurRadius: 12,
                      offset: const Offset(3, 5),
                    ),
                    BoxShadow(
                      color: isDark
                          ? Colors.white.withValues(alpha: 0.03)
                          : Colors.white.withValues(alpha: 0.9),
                      blurRadius: 10,
                      offset: const Offset(-3, -4),
                    ),
                  ],
                ),
                child: Icon(
                  isDark ? Icons.light_mode_rounded : Icons.dark_mode_rounded,
                  color: const Color(0xFF0284C7),
                  size: 20,
                ),
              ),
            ),
          ),

          // 3. MAIN FORM BODY
          Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.symmetric(horizontal: 28.0, vertical: 36.0),
              physics: const BouncingScrollPhysics(),
              child: Container(
                constraints: BoxConstraints(maxWidth: isDesktop ? 420 : double.infinity),
                child: Form(
                  key: _formKey,
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      // SQUIRCLE CLAY 3D LOGO CONTAINER
                      Container(
                        width: 140,
                        height: 140,
                        decoration: BoxDecoration(
                          color: isDark ? const Color(0xFF121C30) : const Color(0xFFEDF3FD),
                          borderRadius: BorderRadius.circular(44),
                          border: Border.all(
                            color: isDark ? Colors.white.withValues(alpha: 0.1) : Colors.white,
                            width: 2.5,
                          ),
                          boxShadow: [
                            BoxShadow(
                              color: isDark
                                  ? Colors.black.withValues(alpha: 0.6)
                                  : const Color(0xFF94A3B8).withValues(alpha: 0.35),
                              blurRadius: 28,
                              spreadRadius: 2,
                              offset: const Offset(10, 16),
                            ),
                            BoxShadow(
                              color: isDark
                                  ? Colors.white.withValues(alpha: 0.04)
                                  : Colors.white.withValues(alpha: 0.95),
                              blurRadius: 22,
                              spreadRadius: 1,
                              offset: const Offset(-8, -10),
                            ),
                            BoxShadow(
                              color: const Color(0xFF0284C7).withValues(alpha: isDark ? 0.15 : 0.12),
                              blurRadius: 36,
                              spreadRadius: 6,
                            ),
                          ],
                        ),
                        child: Center(
                          child: ClipRRect(
                            borderRadius: BorderRadius.circular(28),
                            child: Image.asset(
                              'assets/logo_pettyclaim.png',
                              width: 82,
                              height: 82,
                              fit: BoxFit.contain,
                              errorBuilder: (context, error, stackTrace) {
                                return const Icon(
                                  Icons.account_balance_wallet_rounded,
                                  size: 64,
                                  color: Color(0xFF0284C7),
                                );
                              },
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(height: 28),

                      // TITLE & SUBTITLE
                      Text(
                        'Welcome Back',
                        style: TextStyle(
                          fontSize: 28,
                          fontWeight: FontWeight.w900,
                          color: textDark,
                          letterSpacing: -0.4,
                        ),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        'Sign in to continue with PettyClaim',
                        style: TextStyle(
                          fontSize: 13,
                          color: textMuted,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                      const SizedBox(height: 28),

                      // INPUT EMAIL
                      Container(
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(28),
                          boxShadow: [
                            BoxShadow(
                              color: isDark
                                  ? Colors.black.withValues(alpha: 0.35)
                                  : const Color(0xFF94A3B8).withValues(alpha: 0.12),
                              blurRadius: 14,
                              offset: const Offset(0, 6),
                            ),
                          ],
                        ),
                        child: TextFormField(
                          controller: _emailController,
                          keyboardType: TextInputType.emailAddress,
                          style: TextStyle(color: textDark, fontSize: 13.5, fontWeight: FontWeight.w600),
                          onChanged: (_) {
                            if (_emailServerError != null) {
                              setState(() => _emailServerError = null);
                            }
                          },
                          validator: (val) {
                            if (val == null || val.trim().isEmpty) {
                              return 'Email wajib diisi';
                            }
                            final trimmed = val.trim().toLowerCase();
                            if (!trimmed.endsWith('@gmail.com')) {
                              return 'Gunakan email resmi @gmail.com';
                            }
                            return _emailServerError;
                          },
                          decoration: InputDecoration(
                            hintText: 'Username or Email',
                            hintStyle: TextStyle(color: textMuted.withValues(alpha: 0.7), fontSize: 13),
                            prefixIcon: Icon(Icons.person_outline_rounded, color: textMuted, size: 20),
                            filled: true,
                            fillColor: inputBg,
                            contentPadding: const EdgeInsets.symmetric(horizontal: 22, vertical: 18),
                            enabledBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(28),
                              borderSide: BorderSide(color: inputBorder, width: 1.5),
                            ),
                            focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(28),
                              borderSide: const BorderSide(color: Color(0xFF0284C7), width: 1.8),
                            ),
                            errorBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(28),
                              borderSide: const BorderSide(color: Color(0xFFEF4444), width: 1.5),
                            ),
                            focusedErrorBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(28),
                              borderSide: const BorderSide(color: Color(0xFFEF4444), width: 1.8),
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),

                      // INPUT PASSWORD
                      Container(
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(28),
                          boxShadow: [
                            BoxShadow(
                              color: isDark
                                  ? Colors.black.withValues(alpha: 0.35)
                                  : const Color(0xFF94A3B8).withValues(alpha: 0.12),
                              blurRadius: 14,
                              offset: const Offset(0, 6),
                            ),
                          ],
                        ),
                        child: TextFormField(
                          controller: _passwordController,
                          obscureText: _obscurePassword,
                          style: TextStyle(color: textDark, fontSize: 13.5, fontWeight: FontWeight.w600),
                          onChanged: (_) {
                            if (_passwordServerError != null) {
                              setState(() => _passwordServerError = null);
                            }
                          },
                          validator: (val) {
                            if (val == null || val.trim().isEmpty) {
                              return 'Password wajib diisi';
                            }
                            if (val.length < 6) {
                              return 'Password minimal 6 karakter';
                            }
                            return _passwordServerError;
                          },
                          decoration: InputDecoration(
                            hintText: 'Password',
                            hintStyle: TextStyle(color: textMuted.withValues(alpha: 0.7), fontSize: 13),
                            prefixIcon: Icon(Icons.lock_outline_rounded, color: textMuted, size: 20),
                            suffixIcon: IconButton(
                              icon: Icon(
                                _obscurePassword ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                                color: textMuted,
                                size: 19,
                              ),
                              onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                            ),
                            filled: true,
                            fillColor: inputBg,
                            contentPadding: const EdgeInsets.symmetric(horizontal: 22, vertical: 18),
                            enabledBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(28),
                              borderSide: BorderSide(color: inputBorder, width: 1.5),
                            ),
                            focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(28),
                              borderSide: const BorderSide(color: Color(0xFF0284C7), width: 1.8),
                            ),
                            errorBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(28),
                              borderSide: const BorderSide(color: Color(0xFFEF4444), width: 1.5),
                            ),
                            focusedErrorBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(28),
                              borderSide: const BorderSide(color: Color(0xFFEF4444), width: 1.8),
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(height: 12),

                      // REMEMBER ME & FORGOT PASSWORD
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Row(
                            children: [
                              SizedBox(
                                width: 22,
                                height: 22,
                                child: Checkbox(
                                  value: _rememberMe,
                                  activeColor: const Color(0xFF0284C7),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
                                  side: BorderSide(color: textMuted.withValues(alpha: 0.6), width: 1.5),
                                  onChanged: (v) => setState(() => _rememberMe = v ?? true),
                                ),
                              ),
                              const SizedBox(width: 8),
                              Text(
                                'Remember Me',
                                style: TextStyle(color: textMuted, fontSize: 12, fontWeight: FontWeight.w600),
                              ),
                            ],
                          ),
                          TextButton(
                            onPressed: () {
                              _showTopNotification(
                                context,
                                message: 'Hubungi administrator / Tim IT untuk reset kata sandi.',
                                isSuccess: true,
                              );
                            },
                            style: TextButton.styleFrom(
                              padding: EdgeInsets.zero,
                              minimumSize: Size.zero,
                              tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                            ),
                            child: const Text(
                              'Forgot Password?',
                              style: TextStyle(
                                color: Color(0xFF0284C7),
                                fontSize: 12,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 24),

                      // LOGIN PILL BUTTON
                      AnimatedBuilder(
                        animation: _btnScaleAnimation,
                        builder: (context, child) => Transform.scale(
                          scale: _btnScaleAnimation.value,
                          child: child,
                        ),
                        child: Container(
                          width: double.infinity,
                          height: 56,
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(28),
                            gradient: const LinearGradient(
                              colors: [Color(0xFF2563EB), Color(0xFF0284C7), Color(0xFF38BDF8)],
                              begin: Alignment.centerLeft,
                              end: Alignment.centerRight,
                            ),
                            boxShadow: [
                              BoxShadow(
                                color: const Color(0xFF0284C7).withValues(alpha: 0.45),
                                blurRadius: 20,
                                offset: const Offset(0, 8),
                              ),
                            ],
                          ),
                          child: Material(
                            color: Colors.transparent,
                            borderRadius: BorderRadius.circular(28),
                            child: InkWell(
                              borderRadius: BorderRadius.circular(28),
                              onTapDown: (_) => _btnScaleController.forward(),
                              onTapUp: (_) => _btnScaleController.reverse(),
                              onTapCancel: () => _btnScaleController.reverse(),
                              onTap: _isLoading ? null : _handleLogin,
                              child: Padding(
                                padding: const EdgeInsets.symmetric(horizontal: 20),
                                child: Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    const SizedBox(width: 36),
                                    Text(
                                      _isLoading ? 'Memverifikasi...' : 'Login',
                                      style: const TextStyle(
                                        color: Colors.white,
                                        fontSize: 16,
                                        fontWeight: FontWeight.w900,
                                        letterSpacing: 0.4,
                                      ),
                                    ),
                                    Container(
                                      width: 36,
                                      height: 36,
                                      decoration: BoxDecoration(
                                        color: Colors.white.withValues(alpha: 0.22),
                                        shape: BoxShape.circle,
                                      ),
                                      child: _isLoading
                                          ? const Padding(
                                              padding: EdgeInsets.all(9),
                                              child: CircularProgressIndicator(
                                                strokeWidth: 2,
                                                color: Colors.white,
                                              ),
                                            )
                                          : const Icon(
                                              Icons.arrow_forward_rounded,
                                              color: Colors.white,
                                              size: 18,
                                            ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(height: 24),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ==========================================
// TOAST NOTIFICATION WIDGET
// ==========================================
class _TopToastWidget extends StatefulWidget {
  final String message;
  final bool isSuccess;
  final VoidCallback onDismiss;

  const _TopToastWidget({
    required this.message,
    required this.isSuccess,
    required this.onDismiss,
  });

  @override
  State<_TopToastWidget> createState() => _TopToastWidgetState();
}

class _TopToastWidgetState extends State<_TopToastWidget>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<Offset> _offsetAnimation;
  late Animation<double> _fadeAnimation;
  Timer? _autoDismissTimer;

  @override
  void initState() {
    super.initState();

    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 400),
    );

    _offsetAnimation = Tween<Offset>(
      begin: const Offset(0.0, -0.6),
      end: Offset.zero,
    ).animate(CurvedAnimation(
      parent: _controller,
      curve: Curves.easeOutCubic,
    ));

    _fadeAnimation = CurvedAnimation(
      parent: _controller,
      curve: Curves.easeOut,
    );

    _controller.forward();

    _autoDismissTimer = Timer(const Duration(milliseconds: 3000), () async {
      if (mounted) {
        await _controller.reverse();
        widget.onDismiss();
      }
    });
  }

  @override
  void dispose() {
    _autoDismissTimer?.cancel();
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final themeColor = widget.isSuccess ? const Color(0xFF10B981) : const Color(0xFFEF4444);

    return Positioned(
      top: 36,
      left: 20,
      right: 20,
      child: SlideTransition(
        position: _offsetAnimation,
        child: FadeTransition(
          opacity: _fadeAnimation,
          child: Material(
            color: Colors.transparent,
            child: Align(
              alignment: Alignment.topCenter,
              child: Container(
                constraints: const BoxConstraints(maxWidth: 420),
                padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
                decoration: BoxDecoration(
                  color: isDark ? const Color(0xFF131D31) : Colors.white,
                  borderRadius: BorderRadius.circular(22),
                  border: Border.all(color: themeColor.withValues(alpha: 0.35), width: 1.5),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: isDark ? 0.4 : 0.08),
                      blurRadius: 20,
                      offset: const Offset(0, 8),
                    ),
                  ],
                ),
                child: Row(
                  children: [
                    Icon(
                      widget.isSuccess ? Icons.check_circle_rounded : Icons.error_rounded,
                      color: themeColor,
                      size: 22,
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        widget.message,
                        style: TextStyle(
                          color: isDark ? Colors.white : const Color(0xFF0F172A),
                          fontWeight: FontWeight.w700,
                          fontSize: 13,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}