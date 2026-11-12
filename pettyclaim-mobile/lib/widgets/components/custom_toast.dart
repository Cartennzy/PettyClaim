import 'dart:async';
import 'package:flutter/material.dart';

enum ToastType { success, error, info }

class AppToast {
  static void showSuccess(
    BuildContext context, {
    required String message,
    String? title,
  }) {
    _show(
      context,
      type: ToastType.success,
      title: title ?? 'Berhasil Disimpan',
      message: message,
    );
  }

  static void showError(
    BuildContext context, {
    required String message,
    String? title,
  }) {
    _show(
      context,
      type: ToastType.error,
      title: title ?? 'Terjadi Kesalahan',
      message: message,
    );
  }

  static void showInfo(
    BuildContext context, {
    required String message,
    String? title,
  }) {
    _show(
      context,
      type: ToastType.info,
      title: title ?? 'Informasi Sistem',
      message: message,
    );
  }

  static void _show(
    BuildContext context, {
    required ToastType type,
    required String title,
    required String message,
  }) {
    final overlay = Overlay.of(context);
    late OverlayEntry overlayEntry;

    overlayEntry = OverlayEntry(
      builder: (context) => _ToastWidget(
        type: type,
        title: title,
        message: message,
        onDismiss: () {
          if (overlayEntry.mounted) {
            overlayEntry.remove();
          }
        },
      ),
    );

    overlay.insert(overlayEntry);
  }
}

class _ToastWidget extends StatefulWidget {
  final ToastType type;
  final String title;
  final String message;
  final VoidCallback onDismiss;

  const _ToastWidget({
    required this.type,
    required this.title,
    required this.message,
    required this.onDismiss,
  });

  @override
  State<_ToastWidget> createState() => _ToastWidgetState();
}

class _ToastWidgetState extends State<_ToastWidget>
    with SingleTickerProviderStateMixin {
  late AnimationController _animController;
  late Animation<double> _fadeAnimation;
  late Animation<Offset> _slideAnimation;
  Timer? _dismissTimer;

  @override
  void initState() {
    super.initState();
    _animController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 400),
    );

    _fadeAnimation = CurvedAnimation(
      parent: _animController,
      curve: Curves.easeOutCubic,
    );

    _slideAnimation = Tween<Offset>(
      begin: const Offset(0.0, -0.4),
      end: Offset.zero,
    ).animate(CurvedAnimation(
      parent: _animController,
      curve: Curves.easeOutBack,
    ));

    _animController.forward();

    _dismissTimer = Timer(const Duration(seconds: 4), () {
      _hideToast();
    });
  }

  void _hideToast() {
    if (mounted) {
      _animController.reverse().then((_) {
        widget.onDismiss();
      });
    }
  }

  @override
  void dispose() {
    _dismissTimer?.cancel();
    _animController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    Color primaryColor;
    Color glowColor;
    IconData icon;

    switch (widget.type) {
      case ToastType.success:
        primaryColor = const Color(0xFF10B981);
        glowColor = const Color(0xFF10B981).withValues(alpha: 0.25);
        icon = Icons.check_circle_rounded;
        break;
      case ToastType.error:
        primaryColor = const Color(0xFFEF4444);
        glowColor = const Color(0xFFEF4444).withValues(alpha: 0.25);
        icon = Icons.error_rounded;
        break;
      case ToastType.info:
        primaryColor = const Color(0xFF38BDF8);
        glowColor = const Color(0xFF38BDF8).withValues(alpha: 0.25);
        icon = Icons.info_rounded;
        break;
    }

    return Positioned(
      top: 24,
      right: 24,
      child: Material(
        color: Colors.transparent,
        child: SlideTransition(
          position: _slideAnimation,
          child: FadeTransition(
            opacity: _fadeAnimation,
            child: Container(
              width: 380,
              constraints: const BoxConstraints(maxWidth: 420),
              decoration: BoxDecoration(
                color: const Color(0xFF0F172A).withValues(alpha: 0.94),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(
                  color: const Color(0xFF334155).withValues(alpha: 0.8),
                  width: 1.2,
                ),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.45),
                    offset: const Offset(0, 16),
                    blurRadius: 32,
                  ),
                  BoxShadow(
                    color: glowColor,
                    offset: const Offset(0, 4),
                    blurRadius: 16,
                  ),
                ],
              ),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(16),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Padding(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 16,
                        vertical: 14,
                      ),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(
                              color: primaryColor.withValues(alpha: 0.12),
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(
                                color: primaryColor.withValues(alpha: 0.3),
                                width: 1,
                              ),
                            ),
                            child: Icon(icon, color: primaryColor, size: 20),
                          ),
                          const SizedBox(width: 14),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  widget.title,
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 14,
                                    fontWeight: FontWeight.bold,
                                    letterSpacing: -0.2,
                                  ),
                                ),
                                const SizedBox(height: 3),
                                Text(
                                  widget.message,
                                  style: const TextStyle(
                                    color: Color(0xFF94A3B8),
                                    fontSize: 12.5,
                                    height: 1.35,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          IconButton(
                            splashRadius: 18,
                            padding: EdgeInsets.zero,
                            constraints: const BoxConstraints(),
                            icon: const Icon(
                              Icons.close_rounded,
                              size: 18,
                              color: Color(0xFF64748B),
                            ),
                            onPressed: _hideToast,
                          ),
                        ],
                      ),
                    ),
                    // Modern Progress Bar Timer
                    TweenAnimationBuilder<double>(
                      duration: const Duration(seconds: 4),
                      tween: Tween(begin: 1.0, end: 0.0),
                      builder: (context, val, _) => LinearProgressIndicator(
                        value: val,
                        minHeight: 2.5,
                        backgroundColor: Colors.transparent,
                        valueColor: AlwaysStoppedAnimation<Color>(
                          primaryColor.withValues(alpha: 0.6),
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