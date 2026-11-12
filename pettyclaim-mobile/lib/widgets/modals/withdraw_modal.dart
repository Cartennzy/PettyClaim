import 'dart:convert';
import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:intl/intl.dart';
import '../../services/api_service.dart';
import '../components/custom_toast.dart';

class WithdrawModal {
  static void show(
    BuildContext context, {
    required double balance,
    required String userEmail,
    required bool isDark,
    required Function(double newBalance) onWithdrawSuccess,
  }) {
    final formKey = GlobalKey<FormState>();
    final amountController = TextEditingController();
    bool isSubmitting = false;

    final NumberFormat currencyFormat = NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (modalContext) {
        return StatefulBuilder(
          builder: (modalContext, setModalState) {
            return ClipRRect(
              borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
              child: BackdropFilter(
                filter: ImageFilter.blur(sigmaX: 16, sigmaY: 16),
                child: Container(
                  padding: EdgeInsets.only(
                    left: 24,
                    right: 24,
                    top: 24,
                    bottom: MediaQuery.of(modalContext).viewInsets.bottom + 24,
                  ),
                  decoration: BoxDecoration(
                    color: isDark ? const Color(0xFF0F172A) : Colors.white,
                    borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
                    border: Border.all(color: isDark ? Colors.white12 : const Color(0xFFE2E8F0)),
                  ),
                  child: Form(
                    key: formKey,
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.all(8),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFF10B981).withValues(alpha: 0.15),
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                  child: const Icon(Icons.arrow_upward_rounded, color: Color(0xFF10B981)),
                                ),
                                const SizedBox(width: 12),
                                Text(
                                  'Tarik Saldo Dompet',
                                  style: TextStyle(
                                    color: isDark ? Colors.white : const Color(0xFF0F172A),
                                    fontSize: 18,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ],
                            ),
                            IconButton(
                              icon: Icon(Icons.close, color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B)),
                              onPressed: () => Navigator.pop(modalContext),
                            ),
                          ],
                        ),
                        const SizedBox(height: 6),
                        Text(
                          'Pencairan dana langsung ke rekening gaji Anda.',
                          style: TextStyle(color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B), fontSize: 12),
                        ),
                        const SizedBox(height: 24),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                          decoration: BoxDecoration(
                            color: isDark ? const Color(0xFF1E293B) : const Color(0xFFF1F5F9),
                            borderRadius: BorderRadius.circular(14),
                          ),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text('Saldo Tersedia', style: TextStyle(color: isDark ? Colors.white70 : const Color(0xFF64748B), fontSize: 13)),
                              Text(
                                currencyFormat.format(balance),
                                style: const TextStyle(color: Color(0xFF10B981), fontWeight: FontWeight.bold, fontSize: 15),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 20),
                        TextFormField(
                          controller: amountController,
                          keyboardType: TextInputType.number,
                          style: TextStyle(color: isDark ? Colors.white : const Color(0xFF0F172A), fontWeight: FontWeight.bold, fontSize: 18),
                          validator: (value) {
                            if (value == null || value.trim().isEmpty) return 'Masukkan nominal';
                            double? amount = double.tryParse(value);
                            if (amount == null) return 'Format angka tidak valid';
                            if (amount < 10000) return 'Minimal penarikan Rp 10.000';
                            if (amount > balance) return 'Saldo tidak mencukupi';
                            return null;
                          },
                          decoration: InputDecoration(
                            labelText: 'Nominal Penarikan',
                            labelStyle: TextStyle(color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B)),
                            prefixText: 'Rp ',
                            prefixStyle: TextStyle(color: isDark ? Colors.white : const Color(0xFF0F172A), fontWeight: FontWeight.bold, fontSize: 18),
                            filled: true,
                            fillColor: isDark ? const Color(0xFF070B14) : const Color(0xFFF8FAFC),
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: BorderSide(color: isDark ? Colors.white12 : const Color(0xFFE2E8F0))),
                            enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: BorderSide(color: isDark ? Colors.white12 : const Color(0xFFE2E8F0))),
                          ),
                        ),
                        const SizedBox(height: 24),
                        SizedBox(
                          width: double.infinity,
                          child: ElevatedButton(
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF10B981),
                              padding: const EdgeInsets.symmetric(vertical: 16),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                            ),
                            onPressed: isSubmitting
                                ? null
                                : () async {
                                    if (!formKey.currentState!.validate()) return;
                                    setModalState(() => isSubmitting = true);

                                    try {
                                      final response = await http.post(
                                        Uri.parse('${ApiService.baseUrl}/wallet/withdraw'),
                                        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
                                        body: jsonEncode({
                                          'email': userEmail,
                                          'amount': double.parse(amountController.text),
                                        }),
                                      );

                                      final data = jsonDecode(response.body);
                                      if (!modalContext.mounted) return;

                                      if (response.statusCode == 200 && data['success'] == true) {
                                        Navigator.pop(modalContext);
                                        onWithdrawSuccess((data['new_balance'] as num).toDouble());
                                        AppToast.showSuccess(
                                          context,
                                          title: 'Penarikan Berhasil',
                                          message: 'Dana telah ditransfer ke rekening bank Anda.',
                                        );
                                      } else {
                                        setModalState(() => isSubmitting = false);
                                        AppToast.showError(
                                          context,
                                          title: 'Gagal Menarik Saldo',
                                          message: data['message'] ?? 'Nominal tidak valid atau melebihi limit.',
                                        );
                                      }
                                    } catch (e) {
                                      if (!modalContext.mounted) return;
                                      setModalState(() => isSubmitting = false);
                                      AppToast.showError(context, title: 'Koneksi Error', message: 'Gagal menghubungi server.');
                                    }
                                  },
                            child: isSubmitting
                                ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                                : const Text('Konfirmasi Tarik Saldo', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 15)),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            );
          },
        );
      },
    );
  }
}