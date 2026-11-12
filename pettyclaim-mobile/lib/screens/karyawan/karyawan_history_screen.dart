import 'package:flutter/material.dart';
import '../../services/api_service.dart';
import '../../widgets/components/sweet_alert_dialog.dart';
import '../../widgets/components/interactive_bouncing_button.dart';
import '../../widgets/components/receipt_image_viewer.dart';

class KaryawanHistoryScreen extends StatefulWidget {
  final List<Map<String, dynamic>> claimsHistory;
  final String userEmail;
  final String activeFilter;
  final ValueChanged<String> onFilterChanged;

  const KaryawanHistoryScreen({
    super.key,
    required this.claimsHistory,
    required this.userEmail,
    required this.activeFilter,
    required this.onFilterChanged,
  });

  @override
  State<KaryawanHistoryScreen> createState() => _KaryawanHistoryScreenState();
}

class _KaryawanHistoryScreenState extends State<KaryawanHistoryScreen> {
  List<Map<String, dynamic>> _displayClaims = [];
  bool _isLoading = false;
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    _normalizeInitialClaims();
    _fetchRealtimeClaims();
  }

  void _normalizeInitialClaims() {
    List<Map<String, dynamic>> list = [];
    for (var s in widget.claimsHistory) {
      list.add(_formatClaimMap(s));
    }
    _displayClaims = list;
  }

  Map<String, dynamic> _formatClaimMap(Map<String, dynamic> s) {
    String rawStatus = (s['statusRaw'] ?? s['status'] ?? 'PENDING_VERIFICATION')
        .toString()
        .toUpperCase()
        .trim();

    Color statusColor = const Color(0xFFF59E0B);
    String statusLabel = 'Validasi Finance';

    if (rawStatus == 'VERIFIED' || rawStatus == 'REVIEW_MANAGER') {
      statusColor = const Color(0xFF0284C7);
      statusLabel = 'Review Manager';
    } else if (rawStatus == 'APPROVED' || rawStatus == 'DISETUJUI' || rawStatus == 'DISBURSED' || rawStatus == 'DICAIRKAN') {
      statusColor = const Color(0xFF10B981);
      statusLabel = (rawStatus == 'DISBURSED' || rawStatus == 'DICAIRKAN') ? 'Sudah Dicairkan' : 'Disetujui';
    } else if (rawStatus == 'REJECTED' || rawStatus == 'DITOLAK') {
      statusColor = const Color(0xFFEF4444);
      statusLabel = 'Ditolak';
    } else {
      statusColor = const Color(0xFFF59E0B);
      statusLabel = 'Validasi Finance';
    }

    double amountVal = 0.0;
    if (s['amount'] is num) {
      amountVal = (s['amount'] as num).toDouble();
    } else {
      amountVal = double.tryParse(s['amount'].toString().replaceAll(RegExp(r'[^0-9.]'), '')) ?? 0;
    }

    String formattedAmount =
        'Rp ${amountVal.toInt().toString().replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (Match m) => '${m[1]}.')}';

    String rawUrl = s['receiptUrl'] ??
        s['receipt_url'] ??
        s['receipt_s3_url'] ??
        s['receipt_image_path'] ??
        s['receipt_image'] ??
        '';

    return {
      'id': s['id'] != null
          ? s['id'].toString().substring(0, s['id'].toString().length >= 8 ? 8 : s['id'].toString().length).toUpperCase()
          : 'CLM-NEW',
      'title': s['title'] ?? s['merchant_name'] ?? 'Klaim Struk',
      'category': s['category'] ?? s['notes'] ?? 'Operasional',
      'date': s['date'] ?? s['transaction_date'] ?? '',
      'amount': formattedAmount,
      'status': statusLabel,
      'statusRaw': rawStatus,
      'color': statusColor,
      'notes': s['notes'] ?? 'Pengajuan Struk Kasir',
      'receiptUrl': rawUrl,
      'receiptBase64': s['receiptBase64'] ?? s['receipt_base64'],
    };
  }

  @override
  void didUpdateWidget(covariant KaryawanHistoryScreen oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.claimsHistory.length != oldWidget.claimsHistory.length) {
      _normalizeInitialClaims();
      _fetchRealtimeClaims();
    }
  }

  Future<void> _fetchRealtimeClaims() async {
    if (!mounted) return;
    setState(() => _isLoading = true);
    try {
      final serverData = await ApiService.fetchClaims(email: widget.userEmail);
      if (mounted) {
        List<Map<String, dynamic>> updatedList = [];
        if (serverData.isNotEmpty) {
          for (var s in serverData) {
            updatedList.add(_formatClaimMap(Map<String, dynamic>.from(s)));
          }
        }
        setState(() {
          _displayClaims = updatedList;
        });
      }
    } catch (e) {
      debugPrint('Error fetch realtime claims: $e');
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  void _showDetailModal(BuildContext context, Map<String, dynamic> item, bool isDark) {
    final modalBg = isDark ? const Color(0xFF162238) : Colors.white;
    final borderColor = isDark ? const Color(0xFF233554) : const Color(0xFFE2E8F0);
    final innerBg = isDark ? const Color(0xFF0A1120) : const Color(0xFFF8FAFC);
    final textDark = isDark ? Colors.white : const Color(0xFF0F172A);
    final textMuted = isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B);

    showDialog(
      context: context,
      builder: (context) => Dialog(
        backgroundColor: modalBg,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(24),
          side: BorderSide(color: borderColor, width: 1.2),
        ),
        insetPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 24),
        child: Container(
          width: double.infinity,
          constraints: const BoxConstraints(maxWidth: 520),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Padding(
                padding: const EdgeInsets.fromLTRB(22, 20, 18, 16),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: isDark ? const Color(0xFF0C4A6E) : const Color(0xFFE0F2FE),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            '#${item['id']}',
                            style: TextStyle(
                              color: isDark ? const Color(0xFF38BDF8) : const Color(0xFF0284C7),
                              fontWeight: FontWeight.w800,
                              fontSize: 11.5,
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Text(
                          'Rincian Pengajuan',
                          style: TextStyle(
                            color: textDark,
                            fontSize: 16,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ],
                    ),
                    IconButton(
                      icon: Icon(Icons.close_rounded, color: textMuted, size: 22),
                      onPressed: () => Navigator.pop(context),
                    ),
                  ],
                ),
              ),
              Divider(height: 1, color: borderColor),
              Flexible(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.all(22),
                  physics: const BouncingScrollPhysics(),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: innerBg,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: borderColor),
                        ),
                        child: Column(
                          children: [
                            _buildDetailRow('Merchant / Toko', item['title'] ?? '', isDark),
                            Divider(color: borderColor, height: 16),
                            _buildDetailRow('Kategori Beban', item['category'] ?? '', isDark),
                            Divider(color: borderColor, height: 16),
                            _buildDetailRow('Tanggal Transaksi', item['date'] ?? '', isDark),
                            Divider(color: borderColor, height: 16),
                            _buildDetailRow('Status Validasi', item['status'] ?? '', isDark),
                            Divider(color: borderColor, height: 16),
                            _buildDetailRow('Total Nominal', item['amount'] ?? '', isDark, isHighlight: true),
                          ],
                        ),
                      ),
                      const SizedBox(height: 18),
                      Text(
                        'Bukti Struk Transaksi Digital:',
                        style: TextStyle(
                          color: textDark,
                          fontSize: 13,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                      const SizedBox(height: 8),
                      ClipRRect(
                        borderRadius: BorderRadius.circular(14),
                        child: ReceiptImageViewer(
                          base64String: item['receiptBase64'],
                          imageUrl: item['receiptUrl'],
                          height: 180,
                          isDark: isDark,
                        ),
                      ),
                      const SizedBox(height: 18),
                      Text(
                        'Catatan / Keterangan:',
                        style: TextStyle(
                          color: textMuted,
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      const SizedBox(height: 6),
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: innerBg,
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(color: borderColor),
                        ),
                        child: Text(
                          item['notes'] ?? 'Pengajuan dari Scan Struk OCR',
                          style: TextStyle(
                            color: textDark,
                            fontSize: 12.5,
                            height: 1.45,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              Divider(height: 1, color: borderColor),
              Padding(
                padding: const EdgeInsets.all(18),
                child: SizedBox(
                  width: double.infinity,
                  child: InteractiveBouncingButton(
                    borderRadius: BorderRadius.circular(14),
                    onPressed: () => Navigator.pop(context),
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 13),
                      decoration: BoxDecoration(
                        color: const Color(0xFF0284C7),
                        borderRadius: BorderRadius.circular(14),
                      ),
                      child: const Center(
                        child: Text(
                          'Tutup',
                          style: TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w800,
                            fontSize: 13,
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
    );
  }

  Widget _buildDetailRow(String label, String value, bool isDark, {bool isHighlight = false}) {
    final textMuted = isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B);
    final textDark = isDark ? Colors.white : const Color(0xFF0F172A);

    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label, style: TextStyle(color: textMuted, fontSize: 12)),
        Text(
          value,
          style: TextStyle(
            color: isHighlight ? const Color(0xFF10B981) : textDark,
            fontWeight: FontWeight.w800,
            fontSize: isHighlight ? 15 : 12.5,
          ),
        ),
      ],
    );
  }

  List<Map<String, dynamic>> get _filteredClaims {
    return _displayClaims.where((item) {
      final statusRaw = (item['statusRaw'] ?? '').toString().toUpperCase();

      bool statusMatch = widget.activeFilter == 'SEMUA';
      if (!statusMatch) {
        if (widget.activeFilter == 'PENDING_VERIFICATION') {
          statusMatch = (statusRaw == 'PENDING_VERIFICATION' ||
              statusRaw == 'PENDING' ||
              statusRaw == 'PERLU_VALIDASI');
        } else if (widget.activeFilter == 'VERIFIED') {
          statusMatch = (statusRaw == 'VERIFIED' || statusRaw == 'REVIEW_MANAGER');
        } else if (widget.activeFilter == 'APPROVED') {
          statusMatch = (statusRaw == 'APPROVED' || statusRaw == 'DISETUJUI' || statusRaw == 'DISBURSED' || statusRaw == 'DICAIRKAN');
        } else if (widget.activeFilter == 'REJECTED') {
          statusMatch = (statusRaw == 'REJECTED' || statusRaw == 'DITOLAK');
        } else {
          statusMatch = (statusRaw == widget.activeFilter);
        }
      }

      final searchMatch = _searchQuery.isEmpty ||
          (item['title'] ?? '').toString().toLowerCase().contains(_searchQuery.toLowerCase()) ||
          (item['category'] ?? '').toString().toLowerCase().contains(_searchQuery.toLowerCase());

      return statusMatch && searchMatch;
    }).toList();
  }

  Widget _buildTopHeaderBar(bool isDark) {
    final textDark = isDark ? Colors.white : const Color(0xFF0F172A);
    const avatarBg = Color(0xFF0284C7);

    return Padding(
      padding: const EdgeInsets.only(bottom: 20),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Row(
            children: [
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: avatarBg,
                  borderRadius: BorderRadius.circular(22),
                  border: Border.all(
                    color: const Color(0xFF38BDF8),
                    width: 2,
                  ),
                ),
                child: const Center(
                  child: Icon(
                    Icons.receipt_long_rounded,
                    color: Colors.white,
                    size: 22,
                  ),
                ),
              ),
              const SizedBox(width: 14),
              Text(
                'Riwayat Klaim Struk',
                style: TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.w900,
                  color: textDark,
                  letterSpacing: -0.4,
                ),
              ),
            ],
          ),
          InteractiveBouncingButton(
            borderRadius: BorderRadius.circular(14),
            onPressed: () {
              _fetchRealtimeClaims();
              SweetAlert.show(
                context,
                type: SweetAlertType.info,
                title: 'Data Diperbarui',
                message: 'Riwayat pengajuan berhasil disinkronkan.',
              );
            },
            child: Container(
              width: 42,
              height: 42,
              decoration: BoxDecoration(
                color: isDark ? const Color(0xFF162238) : Colors.white,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(
                  color: isDark ? const Color(0xFF233554) : const Color(0xFFE2E8F0),
                ),
              ),
              child: _isLoading
                  ? const Center(
                      child: SizedBox(
                        width: 16,
                        height: 16,
                        child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFF0284C7)),
                      ),
                    )
                  : Icon(
                      Icons.refresh_rounded,
                      color: isDark ? Colors.white : const Color(0xFF0F172A),
                      size: 20,
                    ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterChip(String rawKey, String label, bool isDark) {
    final isSelected = widget.activeFilter == rawKey;

    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: InkWell(
        onTap: () => widget.onFilterChanged(rawKey),
        borderRadius: BorderRadius.circular(20),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 7),
          decoration: BoxDecoration(
            color: isSelected
                ? const Color(0xFF0284C7)
                : (isDark ? const Color(0xFF162238) : Colors.white),
            borderRadius: BorderRadius.circular(20),
            border: Border.all(
              color: isSelected
                  ? const Color(0xFF0284C7)
                  : (isDark ? const Color(0xFF233554) : const Color(0xFFE2E8F0)),
            ),
            boxShadow: isSelected
                ? [
                    BoxShadow(
                      color: const Color(0xFF0284C7).withValues(alpha: 0.3),
                      blurRadius: 8,
                      offset: const Offset(0, 3),
                    ),
                  ]
                : [],
          ),
          child: Text(
            label,
            style: TextStyle(
              color: isSelected
                  ? Colors.white
                  : (isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B)),
              fontSize: 12,
              fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
            ),
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final textDark = isDark ? Colors.white : const Color(0xFF0F172A);
    final textMuted = isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B);
    final borderColor = isDark ? const Color(0xFF233554) : const Color(0xFFE2E8F0);
    final searchBg = isDark ? const Color(0xFF162238) : Colors.white;
    final cardBg = isDark ? const Color(0xFF162238) : Colors.white;
    final isDesktop = MediaQuery.of(context).size.width > 768;

    final filteredList = _filteredClaims;

    return RefreshIndicator(
      onRefresh: _fetchRealtimeClaims,
      color: const Color(0xFF0284C7),
      backgroundColor: cardBg,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(parent: BouncingScrollPhysics()),
        padding: EdgeInsets.symmetric(
          horizontal: isDesktop ? 36.0 : 20.0,
          vertical: 24.0,
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildTopHeaderBar(isDark),

            Text(
              'Riwayat Dokumen Struk',
              style: TextStyle(
                fontSize: 22,
                fontWeight: FontWeight.w900,
                color: textDark,
                letterSpacing: -0.4,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              '${_displayClaims.length} total pengajuan struk tersinkronisasi server.',
              style: TextStyle(fontSize: 13, color: textMuted),
            ),
            const SizedBox(height: 16),

            // Search Filter Bar Pill Style
            Container(
              decoration: BoxDecoration(
                color: searchBg,
                borderRadius: BorderRadius.circular(30),
                border: Border.all(color: borderColor, width: 1.2),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF0284C7).withValues(alpha: 0.05),
                    blurRadius: 16,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
              child: Row(
                children: [
                  Icon(Icons.search_rounded, color: textMuted, size: 20),
                  const SizedBox(width: 10),
                  Expanded(
                    child: TextField(
                      onChanged: (val) => setState(() => _searchQuery = val),
                      style: TextStyle(color: textDark, fontSize: 13),
                      decoration: InputDecoration(
                        hintText: 'Cari nama merchant atau kategori...',
                        hintStyle: TextStyle(color: textMuted, fontSize: 13),
                        border: InputBorder.none,
                        isDense: true,
                        contentPadding: const EdgeInsets.symmetric(vertical: 8),
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 14),

            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              physics: const BouncingScrollPhysics(),
              child: Row(
                children: [
                  _buildFilterChip('SEMUA', 'Semua Status', isDark),
                  _buildFilterChip('PENDING_VERIFICATION', 'Validasi Finance', isDark),
                  _buildFilterChip('VERIFIED', 'Review Manager', isDark),
                  _buildFilterChip('APPROVED', 'Disetujui / Cair', isDark),
                  _buildFilterChip('REJECTED', 'Ditolak', isDark),
                ],
              ),
            ),
            const SizedBox(height: 22),

            if (_isLoading && _displayClaims.isEmpty)
              const Center(
                child: Padding(
                  padding: EdgeInsets.all(40.0),
                  child: CircularProgressIndicator(color: Color(0xFF0284C7)),
                ),
              )
            else if (filteredList.isEmpty)
              Container(
                padding: const EdgeInsets.symmetric(vertical: 48, horizontal: 24),
                width: double.infinity,
                decoration: BoxDecoration(
                  color: isDark ? const Color(0xFF0A251B) : const Color(0xFFF0FDF4),
                  borderRadius: BorderRadius.circular(22),
                  border: Border.all(
                    color: isDark ? const Color(0xFF065F46) : const Color(0xFFA7F3D0),
                    width: 1.2,
                  ),
                ),
                child: Column(
                  children: [
                    Container(
                      width: 56,
                      height: 56,
                      decoration: BoxDecoration(
                        color: isDark ? const Color(0xFF0D422F) : const Color(0xFFD1FAE5),
                        borderRadius: BorderRadius.circular(18),
                      ),
                      child: const Icon(
                        Icons.done_all_rounded,
                        color: Color(0xFF10B981),
                        size: 32,
                      ),
                    ),
                    const SizedBox(height: 16),
                    Text(
                      'Tidak Ada Transaksi',
                      style: TextStyle(
                        color: isDark ? Colors.white : const Color(0xFF065F46),
                        fontWeight: FontWeight.w900,
                        fontSize: 16,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      'Belum ada pengajuan struk yang sesuai dengan filter.',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF047857),
                        fontSize: 13,
                      ),
                    ),
                  ],
                ),
              )
            else
              ListView.separated(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: filteredList.length,
                separatorBuilder: (context, index) => const SizedBox(height: 12),
                itemBuilder: (context, index) {
                  final claim = filteredList[index];
                  final Color statusColor = claim['color'] as Color? ?? const Color(0xFFF59E0B);

                  return Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: isDark ? const Color(0xFF162238) : const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(18),
                      border: Border.all(
                        color: isDark ? const Color(0xFF233554) : const Color(0xFFE2E8F0),
                        width: 1.2,
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFF64748B).withValues(alpha: 0.05),
                          blurRadius: 10,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: Row(
                      children: [
                        Container(
                          width: 44,
                          height: 44,
                          decoration: BoxDecoration(
                            color: isDark ? const Color(0xFF0C4A6E) : const Color(0xFFE0F2FE),
                            borderRadius: BorderRadius.circular(14),
                          ),
                          child: const Icon(
                            Icons.receipt_long_rounded,
                            color: Color(0xFF0284C7),
                            size: 22,
                          ),
                        ),
                        const SizedBox(width: 14),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                claim['title'] ?? '',
                                style: TextStyle(
                                  color: textDark,
                                  fontWeight: FontWeight.w900,
                                  fontSize: 14,
                                ),
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 3),
                              Text(
                                '${claim['category']} • ${claim['date']}',
                                style: TextStyle(color: textMuted, fontSize: 11.5),
                              ),
                              const SizedBox(height: 4),
                              Row(
                                children: [
                                  Text(
                                    claim['amount'] ?? '',
                                    style: const TextStyle(
                                      color: Color(0xFF10B981),
                                      fontWeight: FontWeight.w900,
                                      fontSize: 14,
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                    decoration: BoxDecoration(
                                      color: statusColor.withValues(alpha: 0.14),
                                      borderRadius: BorderRadius.circular(6),
                                    ),
                                    child: Text(
                                      claim['status'] ?? '',
                                      style: TextStyle(
                                        color: statusColor,
                                        fontSize: 10,
                                        fontWeight: FontWeight.w800,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 10),
                        InteractiveBouncingButton(
                          borderRadius: BorderRadius.circular(12),
                          onPressed: () => _showDetailModal(context, claim, isDark),
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                            decoration: BoxDecoration(
                              color: const Color(0xFF0284C7),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: const Text(
                              'Detail',
                              style: TextStyle(
                                color: Colors.white,
                                fontSize: 12,
                                fontWeight: FontWeight.w800,
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  );
                },
              ),
            const SizedBox(height: 110),
          ],
        ),
      ),
    );
  }
}