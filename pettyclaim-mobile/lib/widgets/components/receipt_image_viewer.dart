import 'dart:convert';
import 'package:flutter/material.dart';

class ReceiptImageViewer extends StatelessWidget {
  final String? base64String;
  final String? imageUrl;
  final double height;
  final BoxFit fit;
  final bool isDark;

  const ReceiptImageViewer({
    super.key,
    this.base64String,
    this.imageUrl,
    this.height = 200,
    this.fit = BoxFit.contain,
    required this.isDark,
  });

  @override
  Widget build(BuildContext context) {
    if (base64String != null && base64String!.startsWith('data:image')) {
      try {
        final bytes = base64Decode(base64String!.split(',').last);
        return Image.memory(
          bytes,
          width: double.infinity,
          height: height,
          fit: fit,
          errorBuilder: (_, __, ___) => _buildPlaceholder(),
        );
      } catch (_) {}
    }

    final url = imageUrl ?? '';
    if (url.isNotEmpty) {
      String fullUrl = url;
      if (!url.startsWith('http://') && !url.startsWith('https://')) {
        fullUrl = 'http://localhost:8000/storage/${url.replaceAll('storage/', '').replaceAll('public/', '')}';
      }

      return Image.network(
        fullUrl,
        width: double.infinity,
        height: height,
        fit: fit,
        loadingBuilder: (_, child, progress) {
          if (progress == null) return child;
          return Container(
            height: height,
            color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF1F5F9),
            child: const Center(
              child: CircularProgressIndicator(color: Color(0xFF0F2C59), strokeWidth: 2),
            ),
          );
        },
        errorBuilder: (_, __, ___) => _buildPlaceholder(),
      );
    }

    return _buildPlaceholder();
  }

  Widget _buildPlaceholder() {
    return Container(
      height: height,
      color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF1F5F9),
      child: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.receipt_long_rounded, color: Color(0xFF0F2C59), size: 36),
            const SizedBox(height: 6),
            Text(
              'Bukti Struk Digital Tersimpan',
              style: TextStyle(
                color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B),
                fontSize: 11,
              ),
            ),
          ],
        ),
      ),
    );
  }
}