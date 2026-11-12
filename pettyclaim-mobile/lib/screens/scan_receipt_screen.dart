import 'dart:io';
import 'package:flutter/material.dart';
import 'package:camera/camera.dart';
import '../services/api_service.dart';
import '../widgets/scanner/scanner_hud_overlay.dart';
import '../widgets/modals/editable_claim_modal.dart';

class ScanReceiptScreen extends StatefulWidget {
  final String userEmail;

  const ScanReceiptScreen({super.key, required this.userEmail});

  @override
  State<ScanReceiptScreen> createState() => _ScanReceiptScreenState();
}

class _ScanReceiptScreenState extends State<ScanReceiptScreen> with SingleTickerProviderStateMixin {
  CameraController? _cameraController;
  late AnimationController _laserAnimationController;

  bool _isCameraInitialized = false;
  bool _isProcessing = false;

  final List<String> _claimCategories = [
    '1101 - Beban Operasional / Dapur',
    '1102 - Beban Alat Tulis Kantor (ATK)',
    '1103 - Beban Bensin & Transportasi',
    '1104 - Beban Kurir & Logistik',
    '1105 - Beban Perbaikan & Pemeliharaan',
  ];

  @override
  void initState() {
    super.initState();
    _laserAnimationController = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 2),
    )..repeat(reverse: true);

    _initializeCamera();
  }

  Future<void> _initializeCamera() async {
    try {
      final cameras = await availableCameras();
      if (cameras.isEmpty) return;

      final backCamera = cameras.firstWhere(
        (camera) => camera.lensDirection == CameraLensDirection.back,
        orElse: () => cameras.first,
      );

      // Resolusi tinggi agar teks kecil pada struk kasir tajam untuk dibaca PaddleOCR
      _cameraController = CameraController(
        backCamera,
        ResolutionPreset.veryHigh,
        enableAudio: false,
      );

      await _cameraController!.initialize();
      if (mounted) {
        setState(() {
          _isCameraInitialized = true;
        });
      }
    } catch (e) {
      debugPrint('Error inisialisasi kamera: $e');
    }
  }

  Future<void> _processScan() async {
    if (_cameraController == null || !_cameraController!.value.isInitialized || _isProcessing) {
      return;
    }

    setState(() {
      _isProcessing = true;
    });

    try {
      final XFile picture = await _cameraController!.takePicture();
      final File imageFile = File(picture.path);
      final imageBytes = await imageFile.readAsBytes();

      // Kirim file foto langsung ke backend Laravel -> PaddleOCR Python Service
      final data = await ApiService.processReceiptScan(
        imageBytes: imageBytes,
        filename: picture.name,
      );

      if (mounted) {
        setState(() { _isProcessing = false; });
        
        if (data != null) {
          _openEditableModal(data, imageFile);
        } else {
          _showToast("Gagal membaca struktur teks struk.", isError: true);
        }
      }
    } catch (e) {
      debugPrint('Error proses scan: $e');
      if (mounted) {
        setState(() { _isProcessing = false; });
        _showToast("Terjadi kendala saat memproses gambar struk.", isError: true);
      }
    }
  }

  void _openEditableModal(Map<String, dynamic> data, File imageFile) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (dialogCtx) {
        return EditableClaimModal(
          initialMerchant: data['merchant_name'] ?? 'Struk Pembelian',
          initialAmount: (data['total_amount'] as num?)?.toDouble() ?? 0.0,
          initialCategory: data['category'] ?? _claimCategories.first,
          initialDate: data['transaction_date'] ?? '',
          categories: _claimCategories,
          onSubmit: (merchant, amount, category, date) async {
            final rootNav = Navigator.of(context);
            final messenger = ScaffoldMessenger.of(context);

            Navigator.pop(dialogCtx);

            final imageBytes = await imageFile.readAsBytes();
            final ok = await ApiService.submitClaim(
              merchantName: merchant,
              amount: amount,
              date: date,
              imageBytes: imageBytes,
              employeeEmail: widget.userEmail,
              notes: "$category - Pengajuan Struk Kasir",
            );

            if (ok) {
              messenger.showSnackBar(
                SnackBar(
                  content: Text("Klaim sebesar Rp ${amount.toInt()} berhasil diajukan!"),
                  backgroundColor: const Color(0xFF10B981),
                ),
              );
              rootNav.pop(true);
            } else {
              messenger.showSnackBar(
                const SnackBar(
                  content: Text("Gagal mengirim klaim ke server."),
                  backgroundColor: Colors.redAccent,
                ),
              );
            }
          },
        );
      },
    );
  }

  void _showToast(String message, {bool isError = false}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: isError ? Colors.redAccent : const Color(0xFF10B981),
      ),
    );
  }

  @override
  void dispose() {
    _cameraController?.dispose();
    _laserAnimationController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;

    return Scaffold(
      backgroundColor: Colors.black,
      body: Stack(
        children: [
          if (_isCameraInitialized)
            SizedBox(
              width: size.width,
              height: size.height,
              child: CameraPreview(_cameraController!),
            )
          else
            const Center(
              child: CircularProgressIndicator(color: Color(0xFF10B981)),
            ),

          if (_isCameraInitialized)
            ScannerHudOverlay(
              boxWidth: size.width * 0.85,
              boxHeight: size.height * 0.6,
              laserAnimation: _laserAnimationController,
            ),

          SafeArea(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
                  child: Row(
                    children: [
                      IconButton(
                        icon: const Icon(Icons.arrow_back_ios_new, color: Colors.white),
                        onPressed: () => Navigator.pop(context),
                      ),
                      const Text(
                        'Scan Struk Pembelian',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                ),

                Padding(
                  padding: const EdgeInsets.only(bottom: 40.0),
                  child: _isProcessing
                      ? const CircularProgressIndicator(color: Color(0xFF10B981))
                      : GestureDetector(
                          onTap: _processScan,
                          child: Container(
                            width: 74,
                            height: 74,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              border: Border.all(color: Colors.white, width: 4),
                              color: const Color(0xFF10B981).withValues(alpha: 0.85),
                            ),
                            child: const Icon(Icons.camera_alt, color: Colors.white, size: 32),
                          ),
                        ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}