import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../services/api_service.dart';
import '../../widgets/components/interactive_bouncing_button.dart';
import '../../widgets/navbars/navbar_manager.dart';
import 'manager_approval_screen.dart';
import 'manager_profile_screen.dart';

class ManagerDashboardScreen extends StatefulWidget {
  final String userEmail;

  const ManagerDashboardScreen({super.key, required this.userEmail});

  @override
  State<ManagerDashboardScreen> createState() => _ManagerDashboardScreenState();
}

class _ManagerDashboardScreenState extends State<ManagerDashboardScreen> {
  int _pendingCount = 0;
  int _approvedCount = 0;
  int _rejectedCount = 0;
  double _totalExpenseAmount = 0.0;
  int _selectedIndex = 0;
  bool _isLoadingStats = false;
  
  String _selectedFilter = 'Semua';
  String _historyFilter = 'SEMUA';

  final NumberFormat _currencyFormat =
      NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);

  @override
  void initState() {
    super.initState();
    _fetchDashboardMetrics();
  }

  Future<void> _fetchDashboardMetrics() async {
    if (!mounted) return;
    setState(() => _isLoadingStats = true);
    try {
      final claims = await ApiService.fetchManagerClaims(managerEmail: widget.userEmail);

      int pending = 0;
      int approved = 0;
      int rejected = 0;
      double totalAmount = 0.0;

      for (var c in claims) {
        String status = (c['status'] ?? 'PENDING').toString().toUpperCase();
        double amount = double.tryParse(c['amount'].toString()) ?? 0.0;

        if (status == 'VERIFIED' || status == 'PENDING' || status == 'PENDING_VERIFICATION') {
          pending++;
        } else if (status == 'APPROVED' || status == 'DISETUJUI' || status == 'DISBURSED' || status == 'DICAIRKAN') {
          approved++;
          totalAmount += amount;
        } else if (status == 'REJECTED' || status == 'DITOLAK') {
          rejected++;
        }
      }

      if (mounted) {
        setState(() {
          _pendingCount = pending;
          _approvedCount = approved;
          _rejectedCount = rejected;
          _totalExpenseAmount = totalAmount;
        });
      }
    } catch (e) {
      debugPrint('Error Fetch Metrics Manager: $e');
    } finally {
      if (mounted) {
        setState(() => _isLoadingStats = false);
      }
    }
  }

  Widget _buildTopBar(bool isDark) {
    final textDark = isDark ? Colors.white : const Color(0xFF0F172A);
    const avatarBg = Color(0xFF0284C7);
    final displayName = widget.userEmail.split('@').first;
    final initialLetter = displayName.isNotEmpty ? displayName[0].toUpperCase() : 'M';

    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
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
                  shape: BoxShape.circle,
                  border: Border.all(color: Colors.white, width: 2),
                  boxShadow: [
                    BoxShadow(
                      color: const Color(0xFF0284C7).withValues(alpha: 0.25),
                      blurRadius: 8,
                      offset: const Offset(0, 3),
                    ),
                  ],
                ),
                child: Center(
                  child: Text(
                    initialLetter,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 18,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Row(
                children: [
                  const Icon(Icons.near_me_rounded, size: 15, color: Color(0xFF0284C7)),
                  const SizedBox(width: 6),
                  Text(
                    displayName,
                    style: TextStyle(
                      fontSize: 17,
                      fontWeight: FontWeight.w800,
                      color: textDark,
                      letterSpacing: -0.3,
                    ),
                  ),
                ],
              ),
            ],
          ),
          InteractiveBouncingButton(
            borderRadius: BorderRadius.circular(22),
            onPressed: _fetchDashboardMetrics,
            child: Container(
              width: 42,
              height: 42,
              decoration: BoxDecoration(
                color: isDark ? const Color(0xFF162238) : Colors.white,
                shape: BoxShape.circle,
                border: Border.all(
                  color: isDark ? const Color(0xFF233554) : const Color(0xFFE2E8F0),
                ),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF0F2C59).withValues(alpha: 0.06),
                    blurRadius: 10,
                    offset: const Offset(0, 3),
                  ),
                ],
              ),
              child: _isLoadingStats
                  ? const Center(
                      child: SizedBox(
                        width: 16,
                        height: 16,
                        child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFF0284C7)),
                      ),
                    )
                  : Icon(
                      Icons.notifications_none_rounded,
                      color: isDark ? Colors.white : const Color(0xFF0F172A),
                      size: 20,
                    ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSearchFilterBar(bool isDark) {
    final searchBg = isDark ? const Color(0xFF162238) : Colors.white;
    final borderColor = isDark ? const Color(0xFF233554) : const Color(0xFFE2E8F0);
    final textMuted = isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B);

    return Container(
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
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
      child: Row(
        children: [
          Icon(Icons.search_rounded, color: textMuted, size: 20),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              'Cari antrean klaim karyawan...',
              style: TextStyle(
                color: textMuted,
                fontSize: 13,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
          InteractiveBouncingButton(
            borderRadius: BorderRadius.circular(18),
            onPressed: () => setState(() => _selectedIndex = 1),
            child: Container(
              width: 34,
              height: 34,
              decoration: const BoxDecoration(
                color: Color(0xFF0284C7),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.tune_rounded, color: Colors.white, size: 16),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCategoryPills(bool isDark) {
    const filters = ['Semua', 'Pending', 'Disetujui', 'Ditolak'];

    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      physics: const BouncingScrollPhysics(),
      child: Row(
        children: filters.map((filter) {
          final isSelected = _selectedFilter == filter;
          return Padding(
            padding: const EdgeInsets.only(right: 8),
            child: InkWell(
              onTap: () {
                String rawKey = 'SEMUA';
                if (filter == 'Pending') {
                  rawKey = 'PENDING_VERIFICATION';
                } else if (filter == 'Disetujui') {
                  rawKey = 'APPROVED';
                } else if (filter == 'Ditolak') {
                  rawKey = 'REJECTED';
                }

                setState(() {
                  _selectedFilter = filter;
                  _historyFilter = rawKey;
                  _selectedIndex = 1;
                });
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
                  filter,
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
        }).toList(),
      ),
    );
  }

  Widget _buildStackedHeroCard(bool isDark) {
    final formattedTotal = _currencyFormat.format(_totalExpenseAmount);

    return Column(
      children: [
        Container(
          margin: const EdgeInsets.symmetric(horizontal: 18),
          height: 8,
          decoration: BoxDecoration(
            color: const Color(0xFF38BDF8).withValues(alpha: isDark ? 0.2 : 0.35),
            borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
          ),
        ),
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(18),
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
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    width: 44,
                    height: 44,
                    decoration: BoxDecoration(
                      color: const Color(0xFF0F172A),
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: Colors.white24, width: 1.2),
                    ),
                    child: const Icon(
                      Icons.shield_rounded,
                      color: Color(0xFF38BDF8),
                      size: 24,
                    ),
                  ),
                  const SizedBox(width: 12),
                  const Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'PettyClaim Corporate',
                          style: TextStyle(
                            color: Colors.white70,
                            fontSize: 11.5,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        SizedBox(height: 2),
                        Text(
                          'Otorisasi Kas Divisi',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 16,
                            fontWeight: FontWeight.w900,
                            letterSpacing: -0.3,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const Icon(Icons.more_horiz_rounded, color: Colors.white70),
                ],
              ),
              const SizedBox(height: 12),
              Wrap(
                spacing: 6,
                children: [
                  _buildHeroChip('Audit SOP'),
                  _buildHeroChip('Pencairan Kas'),
                  _buildHeroChip('Otomatis'),
                ],
              ),
              const SizedBox(height: 16),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        formattedTotal,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 18,
                          fontWeight: FontWeight.w900,
                          letterSpacing: -0.5,
                        ),
                      ),
                      const SizedBox(height: 2),
                      const Text(
                        'Total Saldo Disetujui',
                        style: TextStyle(color: Colors.white70, fontSize: 10.5),
                      ),
                    ],
                  ),
                  InteractiveBouncingButton(
                    borderRadius: BorderRadius.circular(18),
                    onPressed: () {
                      setState(() {
                        _historyFilter = 'PENDING_VERIFICATION';
                        _selectedFilter = 'Pending';
                        _selectedIndex = 1;
                      });
                    },
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(18),
                      ),
                      child: const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            'Review Antrean',
                            style: TextStyle(
                              color: Color(0xFF0284C7),
                              fontWeight: FontWeight.w800,
                              fontSize: 12,
                            ),
                          ),
                          SizedBox(width: 4),
                          Icon(Icons.arrow_outward_rounded, color: Color(0xFF0284C7), size: 14),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildHeroChip(String label) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 3.5),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.18),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Text(
        label,
        style: const TextStyle(
          color: Colors.white,
          fontSize: 10,
          fontWeight: FontWeight.w700,
        ),
      ),
    );
  }

  Widget _buildCleanThemedCard({
    required String title,
    required String countText,
    required String tag,
    required IconData icon,
    required IconData bgWatermarkIcon,
    required Color accentColor,
    required Color cardBg,
    required Color borderColor,
    required Color textDark,
    VoidCallback? onTap,
  }) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: cardBg,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: borderColor, width: 1.2),
        boxShadow: [
          BoxShadow(
            color: accentColor.withValues(alpha: 0.05),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(18),
        child: Stack(
          children: [
            Positioned(
              right: -10,
              bottom: -15,
              child: Icon(
                bgWatermarkIcon,
                size: 105,
                color: accentColor.withValues(alpha: 0.07),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        width: 40,
                        height: 40,
                        decoration: BoxDecoration(
                          color: accentColor.withValues(alpha: 0.14),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Icon(icon, color: accentColor, size: 21),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              title,
                              style: TextStyle(
                                fontSize: 14,
                                fontWeight: FontWeight.w800,
                                color: textDark,
                                letterSpacing: -0.2,
                              ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              countText,
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w900,
                                color: accentColor,
                                letterSpacing: -0.3,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                        decoration: BoxDecoration(
                          color: accentColor.withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          tag,
                          style: TextStyle(
                            color: accentColor,
                            fontSize: 10.5,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ),
                      InteractiveBouncingButton(
                        borderRadius: BorderRadius.circular(12),
                        onPressed: onTap,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
                          decoration: BoxDecoration(
                            color: accentColor,
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: const Text(
                            'Lihat Status',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 11.5,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildManagerOverviewTab(bool isDark, bool isDesktop) {
    final textDark = isDark ? Colors.white : const Color(0xFF0F172A);

    return Stack(
      children: [
        Positioned(
          top: 0,
          left: 0,
          right: 0,
          height: 280,
          child: Container(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: isDark
                    ? const [
                        Color(0xFF0B2544),
                        Color(0x000A1120),
                      ]
                    : const [
                        Color(0xFFBAE6FD),
                        Color(0xFFE0F2FE),
                        Color(0x00F8FAFC),
                      ],
                stops: const [0.0, 0.6, 1.0],
              ),
            ),
          ),
        ),
        SingleChildScrollView(
          physics: const BouncingScrollPhysics(),
          padding: EdgeInsets.symmetric(
            horizontal: isDesktop ? 36.0 : 20.0,
            vertical: 24.0,
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _buildTopBar(isDark),
              _buildSearchFilterBar(isDark),
              const SizedBox(height: 16),
              _buildCategoryPills(isDark),
              const SizedBox(height: 22),

              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Ringkasan Otoritas',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w900,
                      color: textDark,
                      letterSpacing: -0.3,
                    ),
                  ),
                  InkWell(
                    onTap: () => setState(() => _selectedIndex = 1),
                    child: const Row(
                      children: [
                        Text(
                          'Lihat Semua',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w700,
                            color: Color(0xFF0284C7),
                          ),
                        ),
                        SizedBox(width: 2),
                        Icon(Icons.arrow_forward_ios_rounded, size: 10, color: Color(0xFF0284C7)),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),

              _buildStackedHeroCard(isDark),
              const SizedBox(height: 24),

              Text(
                'Antrean & Metrik Terkini',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w900,
                  color: textDark,
                  letterSpacing: -0.3,
                ),
              ),
              const SizedBox(height: 12),

              _buildCleanThemedCard(
                title: 'Pengajuan Menunggu',
                countText: '$_pendingCount Klaim Pending',
                tag: 'Perlu Otorisasi',
                icon: Icons.pending_actions_rounded,
                bgWatermarkIcon: Icons.pending_actions_rounded,
                accentColor: const Color(0xFFD97706),
                cardBg: isDark ? const Color(0xFF291B07) : const Color(0xFFFFFBEB),
                borderColor: isDark ? const Color(0xFF78350F) : const Color(0xFFFDE68A),
                textDark: textDark,
                onTap: () {
                  setState(() {
                    _historyFilter = 'PENDING_VERIFICATION';
                    _selectedFilter = 'Pending';
                    _selectedIndex = 1;
                  });
                },
              ),

              _buildCleanThemedCard(
                title: 'Klaim Disetujui',
                countText: '$_approvedCount Pengajuan Selesai',
                tag: 'Disetujui',
                icon: Icons.check_circle_rounded,
                bgWatermarkIcon: Icons.verified_rounded,
                accentColor: const Color(0xFF059669),
                cardBg: isDark ? const Color(0xFF08261A) : const Color(0xFFECFDF5),
                borderColor: isDark ? const Color(0xFF065F46) : const Color(0xFFA7F3D0),
                textDark: textDark,
                onTap: () {
                  setState(() {
                    _historyFilter = 'APPROVED';
                    _selectedFilter = 'Disetujui';
                    _selectedIndex = 1;
                  });
                },
              ),

              if (_rejectedCount > 0)
                _buildCleanThemedCard(
                  title: 'Pengajuan Ditolak',
                  countText: '$_rejectedCount Pengajuan Ditolak',
                  tag: 'Ditolak',
                  icon: Icons.cancel_rounded,
                  bgWatermarkIcon: Icons.cancel_rounded,
                  accentColor: const Color(0xFFDC2626),
                  cardBg: isDark ? const Color(0xFF330E13) : const Color(0xFFFEF2F2),
                  borderColor: isDark ? const Color(0xFF7F1D1D) : const Color(0xFFFECACA),
                  textDark: textDark,
                  onTap: () {
                    setState(() {
                      _historyFilter = 'REJECTED';
                      _selectedFilter = 'Ditolak';
                      _selectedIndex = 1;
                    });
                  },
                ),

              const SizedBox(height: 110),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildDesktopSidebarItem(int index, IconData icon, String label, bool isDark) {
    final isSelected = _selectedIndex == index;

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
      decoration: BoxDecoration(
        color: isSelected ? const Color(0xFF0284C7) : Colors.transparent,
        borderRadius: BorderRadius.circular(14),
      ),
      child: ListTile(
        onTap: () => setState(() => _selectedIndex = index),
        dense: true,
        leading: Icon(
          icon,
          color: isSelected ? Colors.white : (isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B)),
          size: 20,
        ),
        title: Text(
          label,
          style: TextStyle(
            color: isSelected ? Colors.white : (isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B)),
            fontWeight: isSelected ? FontWeight.w800 : FontWeight.w500,
            fontSize: 13.5,
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDesktop = MediaQuery.of(context).size.width > 768;
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF0A1120) : const Color(0xFFF8FAFC),
      body: Row(
        children: [
          if (isDesktop)
            Container(
              width: 260,
              decoration: BoxDecoration(
                color: isDark ? const Color(0xFF0F172A) : Colors.white,
                border: Border(
                  right: BorderSide(
                    color: isDark ? const Color(0xFF233554) : const Color(0xFFE2E8F0),
                  ),
                ),
              ),
              child: Column(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 28),
                    child: Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: const Color(0xFF0284C7),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(Icons.shield_rounded, color: Colors.white, size: 20),
                        ),
                        const SizedBox(width: 12),
                        Text(
                          'PettyClaim',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.w900,
                            color: isDark ? Colors.white : const Color(0xFF0F172A),
                          ),
                        ),
                      ],
                    ),
                  ),
                  Divider(
                    height: 1,
                    color: isDark ? const Color(0xFF233554) : const Color(0xFFE2E8F0),
                  ),
                  const SizedBox(height: 16),
                  _buildDesktopSidebarItem(0, Icons.home_rounded, 'Home', isDark),
                  _buildDesktopSidebarItem(1, Icons.assignment_turned_in_rounded, 'Approval', isDark),
                  _buildDesktopSidebarItem(2, Icons.person_rounded, 'Profile', isDark),
                ],
              ),
            ),
          Expanded(
            child: Scaffold(
              backgroundColor: Colors.transparent,
              body: Stack(
                children: [
                  Center(
                    child: Container(
                      constraints: BoxConstraints(maxWidth: isDesktop ? 1050 : double.infinity),
                      child: IndexedStack(
                        index: _selectedIndex,
                        children: [
                          _buildManagerOverviewTab(isDark, isDesktop),
                          ManagerApprovalScreen(
                            managerEmail: widget.userEmail,
                            activeFilter: _historyFilter,
                            onFilterChanged: (String newFilter) {
                              setState(() {
                                _historyFilter = newFilter;
                                if (newFilter == 'PENDING_VERIFICATION') {
                                  _selectedFilter = 'Pending';
                                } else if (newFilter == 'APPROVED') {
                                  _selectedFilter = 'Disetujui';
                                } else if (newFilter == 'REJECTED') {
                                  _selectedFilter = 'Ditolak';
                                } else {
                                  _selectedFilter = 'Semua';
                                }
                              });
                            },
                            onClaimsUpdated: () {
                              if (mounted) _fetchDashboardMetrics();
                            },
                          ),
                          ManagerProfileScreen(
                            userEmail: widget.userEmail,
                            approvedCount: _approvedCount,
                            rejectedCount: _rejectedCount,
                            pendingCount: _pendingCount,
                          ),
                        ],
                      ),
                    ),
                  ),
                  if (!isDesktop)
                    Positioned(
                      left: 0,
                      right: 0,
                      bottom: 0,
                      child: NavbarManager(
                        selectedIndex: _selectedIndex,
                        onItemSelected: (index) => setState(() => _selectedIndex = index),
                        isDark: isDark,
                      ),
                    ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}