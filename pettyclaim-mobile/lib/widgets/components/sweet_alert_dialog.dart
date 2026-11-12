import 'package:flutter/material.dart';

enum SweetAlertType { success, error, warning, info }

class SweetAlert {
  static Future<void> show(
    BuildContext context, {
    required SweetAlertType type,
    required String title,
    required String message,
    String confirmText = 'OK',
    VoidCallback? onConfirm,
  }) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    Color iconColor;
    IconData iconData;
    Color iconBgColor;

    switch (type) {
      case SweetAlertType.success:
        iconColor = const Color(0xFF10B981);
        iconData = Icons.check_circle_rounded;
        iconBgColor = const Color(0xFF10B981).withValues(alpha: 0.15);
        break;
      case SweetAlertType.error:
        iconColor = const Color(0xFFEF4444);
        iconData = Icons.cancel_rounded;
        iconBgColor = const Color(0xFFEF4444).withValues(alpha: 0.15);
        break;
      case SweetAlertType.warning:
        iconColor = const Color(0xFFF59E0B);
        iconData = Icons.warning_amber_rounded;
        iconBgColor = const Color(0xFFF59E0B).withValues(alpha: 0.15);
        break;
      case SweetAlertType.info:
        iconColor = const Color(0xFF0284C7);
        iconData = Icons.info_rounded;
        iconBgColor = const Color(0xFF0284C7).withValues(alpha: 0.15);
        break;
    }

    return showDialog(
      context: context,
      barrierDismissible: true,
      builder: (ctx) => Dialog(
        backgroundColor: Colors.transparent,
        insetPadding: const EdgeInsets.symmetric(horizontal: 28, vertical: 24),
        child: Container(
          padding: const EdgeInsets.all(26),
          constraints: const BoxConstraints(maxWidth: 380),
          decoration: BoxDecoration(
            color: isDark ? const Color(0xFF0F172A) : Colors.white,
            borderRadius: BorderRadius.circular(28),
            border: Border.all(color: isDark ? Colors.white12 : const Color(0xFFE2E8F0)),
            boxShadow: [
              BoxShadow(
                color: isDark ? Colors.black.withValues(alpha: 0.6) : Colors.black.withValues(alpha: 0.15),
                blurRadius: 30,
                offset: const Offset(0, 10),
              )
            ],
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 72,
                height: 72,
                decoration: BoxDecoration(
                  color: iconBgColor,
                  shape: BoxShape.circle,
                ),
                child: Icon(iconData, color: iconColor, size: 42),
              ),
              const SizedBox(height: 18),
              Text(
                title,
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 19,
                  fontWeight: FontWeight.w900,
                  color: isDark ? Colors.white : const Color(0xFF0F172A),
                ),
              ),
              const SizedBox(height: 8),
              Text(
                message,
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 13,
                  height: 1.45,
                  color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B),
                ),
              ),
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF0284C7),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    elevation: 0,
                  ),
                  onPressed: () {
                    Navigator.pop(ctx);
                    if (onConfirm != null) onConfirm();
                  },
                  child: Text(
                    confirmText,
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                  ),
                ),
              )
            ],
          ),
        ),
      ),
    );
  }
}