import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../services/api_service.dart';
import '../../widgets/components/interactive_bouncing_button.dart';
import '../../widgets/modals/withdraw_modal.dart';

class KaryawanFinanceScreen extends StatefulWidget {
  final String userEmail;
  final double walletBalance;
  final List<Map<String, dynamic>> claimsHistory;
  final VoidCallback onRefresh;

  const KaryawanFinanceScreen({
    super.key,
    required this.userEmail,
    required this.walletBalance,
    required this.claimsHistory,
    required this.onRefresh,
  });

  @override
  State<KaryawanFinanceScreen> createState() => _KaryawanFinanceScreenState();
}

class _KaryawanFinanceScreenState extends State<KaryawanFinanceScreen> {
  final NumberFormat _currencyFormat =
      NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);

  bool _isLoading = false;
  double _currentBalance = 0.0;
  double _totalApproved = 0.0;
  double _totalPending = 0.0;
  List<dynamic> _mutations = [];

  // Variabel untuk Limit Kas
  double? _limitKasAktif;
  bool _isLoadingLimit = false;

  @override
  void initState() {
    super.initState();
    _currentBalance = widget.walletBalance;
    _fetchFinanceData();
    _fetchLimitData();
  }

  @override
  void didUpdateWidget(covariant KaryawanFinanceScreen oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.walletBalance != widget.walletBalance) {
      _currentBalance = widget.walletBalance;
      _fetchFinanceData();
    }
  }

  Future<void> _fetchFinanceData() async {
    if (!mounted) return;
    setState(() => _isLoading = true);

    try {
      final res = await ApiService.fetchFinanceSummary(widget.userEmail);
      if (res != null && mounted) {
        setState(() {
          _currentBalance = (res['wallet_balance'] as num?)?.toDouble() ?? widget.walletBalance;
          _totalApproved = (res['total_approved'] as num?)?.toDouble() ?? 0.0;
          _totalPending = (res['total_pending'] as num?)?.toDouble() ?? 0.0;
          _mutations = res['mutations'] ?? [];
        });
      }
    } catch (e) {
      debugPrint('Error fetch finance data: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  // Fungsi khusus untuk ngambil limit kas dari API
  Future<void> _fetchLimitData() async {
    if (!mounted) return;
    setState(() => _isLoadingLimit = true);
    
    try {
       final limit = await ApiService.getLimitKas();
       if (mounted && limit != null) {
          setState(() {
            _limitKasAktif = limit;
          });
       }
    } catch (e) {
       debugPrint('Gagal fetch limit: $e');
    } finally {
       if (mounted) setState(() => _isLoadingLimit = false);
    }
  }

  void _showWithdrawModal(bool isDark) {
    WithdrawModal.show(
      context,
      balance: _currentBalance,
      userEmail: widget.userEmail,
      isDark: isDark,
      onWithdrawSuccess: (newBalance) {
        setState(() => _currentBalance = newBalance);
        widget.onRefresh();
        _fetchFinanceData();
        _fetchLimitData(); // Memanggil ulang limit kas jika ada perubahan penarikan
      },
    );
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
                    Icons.account_balance_wallet_rounded,
                    color: Colors.white,
                    size: 22,
                  ),
                ),
              ),
              const SizedBox(width: 14),
              Text(
                'Dompet & Keuangan',
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
              widget.onRefresh();
              _fetchFinanceData();
              _fetchLimitData();
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
              child: _isLoading || _isLoadingLimit
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

  Widget _buildStackedHeroCard(bool isDark) {
    return Column(
      children: [
        // Efek Stack atas card biru
        Container(
          margin: const EdgeInsets.symmetric(horizontal: 18),
          height: 8,
          decoration: BoxDecoration(
            color: const Color(0xFF38BDF8).withValues(alpha: isDark ? 0.2 : 0.35),
            borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
          ),
        ),
        // Card Biru Dompet Utama
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            color: const Color(0xFF0284C7),
            borderRadius: BorderRadius.circular(22),
            boxShadow: [
              BoxShadow(
                color: const Color(0xFF0284C7).withValues(alpha: 0.35),
                blurRadius: 20,
                offset: const Offset(0, 10),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Saldo Dompet Reimbursement',
                    style: TextStyle(
                      color: Colors.white70,
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 3.5),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.2),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Text(
                      'Siap Ditarik',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 10.5,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 10),
              Text(
                _currencyFormat.format(_currentBalance),
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 28,
                  fontWeight: FontWeight.w900,
                  letterSpacing: -0.5,
                ),
              ),
              const SizedBox(height: 18),
              SizedBox(
                width: double.infinity,
                child: InteractiveBouncingButton(
                  borderRadius: BorderRadius.circular(14),
                  onPressed: () => _showWithdrawModal(isDark),
                  child: Container(
                    padding: const EdgeInsets.symmetric(vertical: 13),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(14),
                    ),
                    child: const Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.arrow_upward_rounded, color: Color(0xFF0284C7), size: 18),
                        SizedBox(width: 6),
                        Text(
                          'Tarik Saldo ke Rekening',
                          style: TextStyle(
                            color: Color(0xFF0284C7),
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
          ),
        ),
        const SizedBox(height: 12),
        // WIDGET BARU: KOTAK LIMIT KAS KECIL
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          decoration: BoxDecoration(
            color: isDark ? const Color(0xFF451A03) : const Color(0xFFFFFBEB),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: isDark ? const Color(0xFF78350F) : const Color(0xFFFDE68A)),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Icon(Icons.account_balance_outlined, 
                       color: isDark ? const Color(0xFFFBBF24) : const Color(0xFFD97706), 
                       size: 18),
                  const SizedBox(width: 8),
                  Text(
                    'Limit Kas Perusahaan Bulan Ini',
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w700,
                      color: isDark ? const Color(0xFFFDE68A) : const Color(0xFF92400E),
                    ),
                  ),
                ],
              ),
              _isLoadingLimit 
                  ? const SizedBox(width: 12, height: 12, child: CircularProgressIndicator(strokeWidth: 2))
                  : Text(
                      _limitKasAktif != null ? _currencyFormat.format(_limitKasAktif) : 'Rp -',
                      style: TextStyle(
                        fontSize: 13.5,
                        fontWeight: FontWeight.w900,
                        color: isDark ? const Color(0xFFF59E0B) : const Color(0xFFB45309),
                      ),
                    ),
            ],
          ),
        ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final textDark = isDark ? Colors.white : const Color(0xFF0F172A);
    final textMuted = isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B);
    final cardBg = isDark ? const Color(0xFF162238) : Colors.white;
    final borderColor = isDark ? const Color(0xFF233554) : const Color(0xFFE2E8F0);
    final isDesktop = MediaQuery.of(context).size.width > 768;

    return RefreshIndicator(
      onRefresh: () async {
        widget.onRefresh();
        await _fetchFinanceData();
        await _fetchLimitData();
      },
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

            _buildStackedHeroCard(isDark),
            const SizedBox(height: 20),

            Row(
              children: [
                Expanded(
                  child: Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: cardBg,
                      borderRadius: BorderRadius.circular(18),
                      border: Border.all(color: borderColor, width: 1.2),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Container(
                          width: 36,
                          height: 36,
                          decoration: BoxDecoration(
                            color: const Color(0xFF10B981).withValues(alpha: 0.15),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(Icons.check_circle_rounded, color: Color(0xFF10B981), size: 18),
                        ),
                        const SizedBox(height: 12),
                        Text('Total Disetujui', style: TextStyle(color: textMuted, fontSize: 11.5)),
                        const SizedBox(height: 2),
                        Text(
                          _currencyFormat.format(_totalApproved),
                          style: TextStyle(color: textDark, fontSize: 15, fontWeight: FontWeight.w900),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: cardBg,
                      borderRadius: BorderRadius.circular(18),
                      border: Border.all(color: borderColor, width: 1.2),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Container(
                          width: 36,
                          height: 36,
                          decoration: BoxDecoration(
                            color: const Color(0xFFF59E0B).withValues(alpha: 0.15),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(Icons.hourglass_top_rounded, color: Color(0xFFF59E0B), size: 18),
                        ),
                        const SizedBox(height: 12),
                        Text('Dalam Review', style: TextStyle(color: textMuted, fontSize: 11.5)),
                        const SizedBox(height: 2),
                        Text(
                          _currencyFormat.format(_totalPending),
                          style: TextStyle(color: textDark, fontSize: 15, fontWeight: FontWeight.w900),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 24),

            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Riwayat Mutasi Saldo',
                  style: TextStyle(
                    fontSize: 17,
                    fontWeight: FontWeight.w900,
                    color: textDark,
                    letterSpacing: -0.3,
                  ),
                ),
                Text(
                  '${_mutations.length} Transaksi',
                  style: TextStyle(color: textMuted, fontSize: 12, fontWeight: FontWeight.w700),
                ),
              ],
            ),
            const SizedBox(height: 12),

            if (_mutations.isEmpty)
              Container(
                padding: const EdgeInsets.symmetric(vertical: 40, horizontal: 24),
                width: double.infinity,
                decoration: BoxDecoration(
                  color: isDark ? const Color(0xFF0A251B) : const Color(0xFFF0FDF4),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(
                    color: isDark ? const Color(0xFF065F46) : const Color(0xFFA7F3D0),
                    width: 1.2,
                  ),
                ),
                child: Column(
                  children: [
                    Icon(Icons.account_balance_wallet_outlined, color: textMuted, size: 36),
                    const SizedBox(height: 12),
                    Text(
                      'Belum Ada Mutasi Saldo',
                      style: TextStyle(color: textDark, fontWeight: FontWeight.w800, fontSize: 14),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Pencairan klaim atau penarikan dana akan tercatat di sini.',
                      style: TextStyle(color: textMuted, fontSize: 12),
                    ),
                  ],
                ),
              )
            else
              ListView.separated(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: _mutations.length,
                separatorBuilder: (context, index) => const SizedBox(height: 10),
                itemBuilder: (context, index) {
                  final item = _mutations[index];
                  final bool isIncome = (item['type'] == 'IN');
                  final Color actionColor = isIncome ? const Color(0xFF10B981) : const Color(0xFFEF4444);
                  final double amt = (item['amount'] is num) ? (item['amount'] as num).toDouble() : 0.0;

                  return Container(
                    padding: const EdgeInsets.all(15),
                    decoration: BoxDecoration(
                      color: isDark ? const Color(0xFF162238) : const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(18),
                      border: Border.all(color: borderColor, width: 1.2),
                    ),
                    child: Row(
                      children: [
                        Container(
                          width: 40,
                          height: 40,
                          decoration: BoxDecoration(
                            color: actionColor.withValues(alpha: 0.15),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Icon(
                            isIncome ? Icons.arrow_downward_rounded : Icons.arrow_upward_rounded,
                            color: actionColor,
                            size: 18,
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                item['title'] ?? 'Mutasi Dompet',
                                style: TextStyle(color: textDark, fontWeight: FontWeight.w800, fontSize: 13.5),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                '${item['formatted_date'] ?? item['transaction_date']} • ${item['channel'] ?? 'Sistem'}',
                                style: TextStyle(color: textMuted, fontSize: 11),
                              ),
                            ],
                          ),
                        ),
                        Text(
                          '${isIncome ? '+' : '-'} ${_currencyFormat.format(amt)}',
                          style: TextStyle(
                            color: actionColor,
                            fontWeight: FontWeight.w900,
                            fontSize: 13.5,
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