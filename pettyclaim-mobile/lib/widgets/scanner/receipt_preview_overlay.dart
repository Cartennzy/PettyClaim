import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:camera/camera.dart';

class ReceiptPreviewOverlay extends StatelessWidget {
  final XFile capturedImage;
  final bool isProcessing;
  final VoidCallback onRetake;
  final VoidCallback onVerify;

  const ReceiptPreviewOverlay({
    super.key,
    required this.capturedImage,
    required this.isProcessing,
    required this.onRetake,
    required this.onVerify,
  });

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: [
        // 1. Gambar Preview Full Screen
        Positioned.fill(
          child: Container(
            color: const Color(0xFF0F172A),
            child: Center(
              child: kIsWeb
                  ? Image.network(capturedImage.path, fit: BoxFit.contain)
                  : Image.file(File(capturedImage.path), fit: BoxFit.contain),
            ),
          ),
        ),

        // 2. Tombol Aksi Bawah
        Positioned(
          bottom: 30,
          left: 24,
          right: 24,
          child: Row(
            children: [
              Expanded(
                flex: 1,
                child: OutlinedButton.icon(
                  style: OutlinedButton.styleFrom(
                    side: const BorderSide(color: Colors.white38),
                    backgroundColor: Colors.black.withValues(alpha: 0.5),
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                  ),
                  onPressed: isProcessing ? null : onRetake,
                  icon: const Icon(Icons.refresh_rounded, color: Colors.white, size: 20),
                  label: const Text('Foto Ulang', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                flex: 2,
                child: ElevatedButton.icon(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF10B981),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                    elevation: 4,
                  ),
                  onPressed: isProcessing ? null : onVerify,
                  icon: const Icon(Icons.auto_awesome, color: Colors.white, size: 20),
                  label: const Text(
                    'Verifikasi Struk AI',
                    style: TextStyle(fontWeight: FontWeight.w900, fontSize: 15),
                  ),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}