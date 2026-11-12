import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../services/api_service.dart';
import '../../widgets/components/sweet_alert_dialog.dart';
import '../../widgets/components/interactive_bouncing_button.dart';
import '../../widgets/components/receipt_image_viewer.dart';

class ManagerApprovalScreen extends StatefulWidget {
  final String? managerEmail;
  final String activeFilter;
  final ValueChanged<String>? onFilterChanged;
  final VoidCallback? onClaimsUpdated;

  const ManagerApprovalScreen({
    super.key, 
    this.managerEmail, 
    this.activeFilter = 'SEMUA',
    this.onFilterChanged,
    this.onClaimsUpdated,
  });

  @override
  State<ManagerApprovalScreen> createState() => _ManagerApprovalScreenState();
}

class _ManagerApprovalScreenState extends State<ManagerApprovalScreen> {
  bool _isSyncing = false;
  bool _isLoading = false;
  String _searchQuery = '';

  List<Map<String, dynamic>> _displayClaims = [];

  final NumberFormat _currencyFormat =
      NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);

  @override
  void initState() {
    super.initState();
    _fetchRealtimePendingClaims();
  }

  Future<void> _fetchRealtimePendingClaims() async {
    if (!mounted) return;
    setState(() => _isLoading = true);
    try {
      final serverData = await ApiService.fetchManagerClaims(managerEmail: widget.managerEmail);

      if (!mounted) return;

      List<Map<String, dynamic>> fetchedList = [];

      if (serverData.isNotEmpty) {
        for (var item in serverData) {
          String statusRaw = (item['status'] ?? '').toString().toUpperCase();
          
          String statusLabel = 'Menunggu Validasi';
          Color statusColor = const Color(0xFFF59E0B);
          
          if (statusRaw == 'VERIFIED' || statusRaw == 'PENDING' || statusRaw == 'PENDING_VERIFICATION') {
              statusLabel = 'Perlu Otorisasi';
              statusColor = const Color(0xFFF59E0B);
          } else if (statusRaw == 'APPROVED' || statusRaw == 'DISETUJUI' || statusRaw == 'DISBURSED' || statusRaw == 'DICAIRKAN') {
              statusLabel = 'Disetujui';
              statusColor = const Color(0xFF10B981);
          } else if (statusRaw == 'REJECTED' || statusRaw == 'DITOLAK') {
              statusLabel = 'Ditolak';
              statusColor = const Color(0xFFEF4444);
          }

          double amountVal = double.tryParse(item['amount'].toString()) ?? 0;
          String formattedAmount = _currencyFormat.format(amountVal);

          String rawUrl = item['receipt_url'] ??
              item['receipt_image_path'] ??
              item['receipt_image'] ??
              '';

          fetchedList.add({
            'id': item['id'] != null
                ? item['id'].toString().substring(0, item['id'].toString().length >= 8 ? 8 : item['id'].toString().length).toUpperCase()
                : 'CLM-NEW',
            'raw_id': item['id']?.toString() ?? '',
            'employee': item['user'] != null ? item['user']['name'] ?? 'Karyawan Pemohon' : 'Karyawan Pemohon',
            'email': item['user'] != null ? item['user']['email'] ?? 'karyawan@company.com' : 'karyawan@company.com',
            'department': item['user'] != null ? item['user']['department'] ?? '-' : '-',
            'category': item['category'] ?? 'Operasional',
            'title': item['merchant_name'] ?? 'Pengajuan Struk',
            'amount': formattedAmount,
            'raw_amount': amountVal,
            'date': item['transaction_date'] ?? '',
            'notes': item['notes'] ?? 'Pengajuan dari Scan Struk OCR',
            'merchant': item['merchant_name'] ?? 'Merchant',
            'status': statusLabel,
            'raw_status': statusRaw,
            'color': statusColor,
            'receiptUrl': rawUrl,
            'receiptBase64': item['receipt_base64'],
          });
        }
      }

      setState(() {
        _displayClaims = fetchedList;
      });

      if (widget.onClaimsUpdated != null) {
        widget.onClaimsUpdated!();
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _displayClaims = [];
        });
      }
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  Future<void> _processClaimAction(Map<String, dynamic> claim, String status) async {
    setState(() => _isSyncing = true);

    final String statusValue = status == 'approved' ? 'APPROVED' : 'REJECTED';

    try {
      final isSuccess = await ApiService.syncApproveClaim(
        claimId: claim['raw_id'],
        employeeEmail: claim['email'] ?? 'karyawan@company.com',
        merchantName: claim['merchant'] ?? 'Merchant',
        amount: (claim['raw_amount'] is num) ? (claim['raw_amount'] as num).toDouble() : 150000.0,
        status: statusValue,
        notes: claim['notes'],
        receiptUrl: claim['receiptUrl'],
      );

      if (!mounted) return;
      setState(() => _isSyncing = false);

      if (isSuccess) {
        await _fetchRealtimePendingClaims();

        if (!mounted) return;
        Navigator.of(context, rootNavigator: true).pop();

        if (statusValue == 'APPROVED') {
          SweetAlert.show(
            context,
            type: SweetAlertType.success,
            title: 'Klaim Disetujui',
            message: 'Klaim ${claim['merchant']} disetujui & diteruskan ke Finance.',
          );
        } else {
          SweetAlert.show(
            context,
            type: SweetAlertType.error,
            title: 'Klaim Ditolak',
            message: 'Pengajuan klaim ${claim['merchant']} telah ditolak.',
          );
        }
      } else {
        SweetAlert.show(
          context,
          type: SweetAlertType.error,
          title: 'Gagal Memproses',
          message: 'Server menolak permintaan otorisasi persetujuan.',
        );
      }
    } catch (e) {
      if (!mounted) return;
      setState(() => _isSyncing = false);
      Navigator.of(context, rootNavigator: true).pop();
      SweetAlert.show(
        context,
        type: SweetAlertType.error,
        title: 'Terjadi Kesalahan',
        message: '$e',
      );
    }
  }

  void _showApprovalDetailModal(BuildContext context, Map<String, dynamic> claim, bool isDark) {
    final modalBg = isDark ? const Color(0xFF162238) : Colors.white;
    final borderColor = isDark ? const Color(0xFF233554) : const Color(0xFFE2E8F0);
    final innerBg = isDark ? const Color(0xFF0A1120) : const Color(0xFFF8FAFC);
    final textDark = isDark ? Colors.white : const Color(0xFF0F172A);
    final textMuted = isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B);

    final bool isPending = claim['raw_status'] == 'VERIFIED' || claim['raw_status'] == 'PENDING' || claim['raw_status'] == 'PENDING_VERIFICATION';

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Dialog(
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
                                  '#${claim['id']}',
                                  style: TextStyle(
                                    color: isDark ? const Color(0xFF38BDF8) : const Color(0xFF0284C7),
                                    fontWeight: FontWeight.w800,
                                    fontSize: 11.5,
                                  ),
                                ),
                              ),
                              const SizedBox(width: 10),
                              Text(
                                'Detail Pengajuan',
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
                                  _buildDetailRow('Pemohon', claim['employee'], isDark),
                                  Divider(color: borderColor, height: 16),
                                  _buildDetailRow('Divisi / Departemen', claim['department'], isDark),
                                  Divider(color: borderColor, height: 16),
                                  _buildDetailRow('Kategori Beban', claim['category'], isDark),
                                  Divider(color: borderColor, height: 16),
                                  _buildDetailRow('Merchant / Toko', claim['merchant'], isDark),
                                  Divider(color: borderColor, height: 16),
                                  _buildDetailRow('Tanggal Transaksi', claim['date'], isDark),
                                  Divider(color: borderColor, height: 16),
                                  _buildDetailRow('Total Nominal', claim['amount'], isDark, isHighlight: true),
                                ],
                              ),
                            ),
                            const SizedBox(height: 18),
                            Text(
                              'Bukti Struk Transaksi:',
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
                                base64String: claim['receiptBase64'],
                                imageUrl: claim['receiptUrl'],
                                height: 180,
                                isDark: isDark,
                              ),
                            ),
                            const SizedBox(height: 18),
                            Text(
                              'Keterangan Pemohon:',
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
                                claim['notes'],
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
                      child: isPending ? Row(
                        children: [
                          Expanded(
                            child: InteractiveBouncingButton(
                              borderRadius: BorderRadius.circular(14),
                              onPressed: _isSyncing ? null : () => _processClaimAction(claim, 'rejected'),
                              child: Container(
                                padding: const EdgeInsets.symmetric(vertical: 13),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFEF4444),
                                  borderRadius: BorderRadius.circular(14),
                                ),
                                child: const Row(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    Icon(Icons.close_rounded, color: Colors.white, size: 18),
                                    SizedBox(width: 6),
                                    Text(
                                      'Tolak',
                                      style: TextStyle(
                                        color: Colors.white,
                                        fontWeight: FontWeight.w800,
                                        fontSize: 13,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            flex: 2,
                            child: InteractiveBouncingButton(
                              borderRadius: BorderRadius.circular(14),
                              onPressed: _isSyncing ? null : () => _processClaimAction(claim, 'approved'),
                              child: Container(
                                padding: const EdgeInsets.symmetric(vertical: 13),
                                decoration: BoxDecoration(
                                  color: const Color(0xFF0284C7),
                                  borderRadius: BorderRadius.circular(14),
                                ),
                                child: Row(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    _isSyncing
                                        ? const SizedBox(
                                            width: 16,
                                            height: 16,
                                            child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                                          )
                                        : const Icon(Icons.check_circle_rounded, color: Colors.white, size: 18),
                                    const SizedBox(width: 8),
                                    Text(
                                      _isSyncing ? 'Memproses...' : 'Setujui & Jurnal',
                                      style: const TextStyle(
                                        color: Colors.white,
                                        fontWeight: FontWeight.w800,
                                        fontSize: 13,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          ),
                        ],
                      ) : SizedBox(
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
            );
          },
        );
      },
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
      final statusRaw = (item['raw_status'] ?? '').toString().toUpperCase();

      bool statusMatch = widget.activeFilter == 'SEMUA';
      if (!statusMatch) {
        if (widget.activeFilter == 'PENDING_VERIFICATION') {
          statusMatch = (statusRaw == 'PENDING_VERIFICATION' || statusRaw == 'PENDING' || statusRaw == 'VERIFIED');
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
          (item['employee'] ?? '').toString().toLowerCase().contains(_searchQuery.toLowerCase());

      return statusMatch && searchMatch;
    }).toList();
  }

  Widget _buildFilterChip(String rawKey, String label, bool isDark) {
    final isSelected = widget.activeFilter == rawKey;

    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: InkWell(
        onTap: () {
          if (widget.onFilterChanged != null) {
            widget.onFilterChanged!(rawKey);
          }
        },
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
    final cardBg = isDark ? const Color(0xFF162238) : Colors.white;
    final borderColor = isDark ? const Color(0xFF233554) : const Color(0xFFE2E8F0);
    final searchBg = isDark ? const Color(0xFF162238) : Colors.white;
    final isDesktop = MediaQuery.of(context).size.width > 768;

    final filteredList = _filteredClaims;

    return RefreshIndicator(
      onRefresh: _fetchRealtimePendingClaims,
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
            Padding(
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
                          color: const Color(0xFF0284C7),
                          borderRadius: BorderRadius.circular(22),
                          border: Border.all(
                            color: const Color(0xFF38BDF8),
                            width: 2,
                          ),
                        ),
                        child: const Center(
                          child: Icon(
                            Icons.assignment_turned_in_rounded,
                            color: Colors.white,
                            size: 22,
                          ),
                        ),
                      ),
                      const SizedBox(width: 14),
                      Text(
                        'Audit & Otorisasi',
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
                    onPressed: _fetchRealtimePendingClaims,
                    child: Container(
                      width: 42,
                      height: 42,
                      decoration: BoxDecoration(
                        color: isDark ? const Color(0xFF162238) : Colors.white,
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(
                          color: isDark ? const Color(0xFF233554) : const Color(0xFFE2E8F0),
                        ),
                        boxShadow: isDark
                            ? []
                            : [
                                BoxShadow(
                                  color: const Color(0xFF64748B).withValues(alpha: 0.08),
                                  blurRadius: 10,
                                  offset: const Offset(0, 3),
                                ),
                              ],
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
            ),

            Text(
              'Antrean Pengajuan Masuk',
              style: TextStyle(
                fontSize: 22,
                fontWeight: FontWeight.w900,
                color: textDark,
                letterSpacing: -0.4,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              '${_displayClaims.length} berkas struk divisi disinkronisasi server.',
              style: TextStyle(fontSize: 13, color: textMuted),
            ),
            const SizedBox(height: 16),

            // Search Bar
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
                        hintText: 'Cari nama pengaju atau merchant...',
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

            // Filter Chips
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              physics: const BouncingScrollPhysics(),
              child: Row(
                children: [
                  _buildFilterChip('SEMUA', 'Semua Status', isDark),
                  _buildFilterChip('PENDING_VERIFICATION', 'Perlu Otorisasi', isDark),
                  _buildFilterChip('APPROVED', 'Disetujui', isDark),
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
                      'Antrean Bersih',
                      style: TextStyle(
                        color: isDark ? Colors.white : const Color(0xFF065F46),
                        fontWeight: FontWeight.w900,
                        fontSize: 16,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      'Tidak ada pengajuan reimbursement yang sesuai dengan pencarian Anda.',
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
                  final bool isPending = claim['raw_status'] == 'VERIFIED' || claim['raw_status'] == 'PENDING' || claim['raw_status'] == 'PENDING_VERIFICATION';

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
                                claim['title'],
                                style: TextStyle(
                                  color: textDark,
                                  fontWeight: FontWeight.w900,
                                  fontSize: 14,
                                ),
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 3),
                              Text(
                                '${claim['employee']} (${claim['department']}) • ${claim['date']}',
                                style: TextStyle(color: textMuted, fontSize: 11.5),
                              ),
                              const SizedBox(height: 4),
                              Row(
                                children: [
                                  Text(
                                    claim['amount'],
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
                          onPressed: () => _showApprovalDetailModal(context, claim, isDark),
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                            decoration: BoxDecoration(
                              color: const Color(0xFF0284C7),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Text(
                              isPending ? 'Audit' : 'Detail',
                              style: const TextStyle(
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