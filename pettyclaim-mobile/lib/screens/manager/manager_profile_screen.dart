import 'package:flutter/material.dart';
import '../../main.dart';
import '../../services/api_service.dart';
import '../../widgets/components/interactive_bouncing_button.dart';
import '../auth/login_screen.dart';

class ManagerProfileScreen extends StatefulWidget {
  final String userEmail;
  final int approvedCount;
  final int rejectedCount;
  final int pendingCount;

  const ManagerProfileScreen({
    super.key,
    required this.userEmail,
    required this.approvedCount,
    required this.rejectedCount,
    required this.pendingCount,
  });

  @override
  State<ManagerProfileScreen> createState() => _ManagerProfileScreenState();
}

class _ManagerProfileScreenState extends State<ManagerProfileScreen> {
  Map<String, dynamic>? _userProfile;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _fetchProfile();
  }

  Future<void> _fetchProfile() async {
    setState(() => _isLoading = true);
    final data = await ApiService.fetchUserProfile(widget.userEmail);
    if (mounted) {
      setState(() {
        _userProfile = data;
        _isLoading = false;
      });
    }
  }

  // Fungsi Parser Ekstra Aman untuk Cek Berbagai Nama Kolom Backend
  String _getSafeString(List<String> keys, {String fallback = '-'}) {
    if (_userProfile == null) return fallback;
    for (String key in keys) {
      final value = _userProfile![key];
      if (value != null &&
          value.toString().trim().isNotEmpty &&
          value.toString().trim() != '-' &&
          value.toString().toLowerCase() != 'null') {
        return value.toString().trim();
      }
    }
    return fallback;
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
                    Icons.person_rounded,
                    color: Colors.white,
                    size: 22,
                  ),
                ),
              ),
              const SizedBox(width: 14),
              Text(
                'Profil & Otoritas',
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
            onPressed: _fetchProfile,
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

  Widget _buildProfileHeroCard(bool isDark, String name, String department) {
    final initialLetter = name.isNotEmpty ? name[0].toUpperCase() : 'M';

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: const Color(0xFF0284C7),
        borderRadius: BorderRadius.circular(24),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0284C7).withValues(alpha: 0.35),
            blurRadius: 18,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Column(
        children: [
          Container(
            width: 76,
            height: 76,
            decoration: BoxDecoration(
              color: Colors.white,
              shape: BoxShape.circle,
              border: Border.all(color: const Color(0xFF38BDF8), width: 3),
            ),
            child: Center(
              child: Text(
                initialLetter,
                style: const TextStyle(
                  color: Color(0xFF0284C7),
                  fontSize: 32,
                  fontWeight: FontWeight.w900,
                ),
              ),
            ),
          ),
          const SizedBox(height: 14),
          Text(
            name,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 20,
              fontWeight: FontWeight.w900,
              letterSpacing: -0.3,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            'Manager Approver • $department',
            style: const TextStyle(
              color: Color(0xFFBAE6FD),
              fontSize: 12.5,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 20),
          Container(
            padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 16),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.15),
              borderRadius: BorderRadius.circular(16),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _buildStatItem('Disetujui', '${widget.approvedCount}'),
                _buildStatDivider(),
                _buildStatItem('Menunggu', '${widget.pendingCount}'),
                _buildStatDivider(),
                _buildStatItem('Ditolak', '${widget.rejectedCount}'),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatItem(String label, String value) {
    return Column(
      children: [
        Text(
          value,
          style: const TextStyle(
            color: Colors.white,
            fontSize: 18,
            fontWeight: FontWeight.w900,
          ),
        ),
        const SizedBox(height: 2),
        Text(
          label,
          style: const TextStyle(
            color: Colors.white70,
            fontSize: 11,
            fontWeight: FontWeight.w600,
          ),
        ),
      ],
    );
  }

  Widget _buildStatDivider() {
    return Container(
      height: 26,
      width: 1,
      color: Colors.white24,
    );
  }

  Widget _buildInfoTile({
    required IconData icon,
    required String title,
    required String subtitle,
    required bool isDark,
    required Color textDark,
    required Color textMuted,
  }) {
    return ListTile(
      contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 6),
      leading: Container(
        width: 40,
        height: 40,
        decoration: BoxDecoration(
          color: isDark ? const Color(0xFF0C4A6E) : const Color(0xFFE0F2FE),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Icon(icon, color: const Color(0xFF0284C7), size: 20),
      ),
      title: Text(
        title,
        style: TextStyle(
          color: textDark,
          fontSize: 14,
          fontWeight: FontWeight.w800,
        ),
      ),
      subtitle: Text(
        subtitle,
        style: TextStyle(
          color: textMuted,
          fontSize: 12,
          fontWeight: FontWeight.w500,
        ),
      ),
    );
  }

  void _showLogoutConfirmation(BuildContext context, bool isDark) {
    final modalBg = isDark ? const Color(0xFF162238) : Colors.white;
    final borderColor = isDark ? const Color(0xFF233554) : const Color(0xFFE2E8F0);
    final textDark = isDark ? Colors.white : const Color(0xFF0F172A);
    final textMuted = isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B);

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: modalBg,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(22),
          side: BorderSide(color: borderColor, width: 1.2),
        ),
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: const Color(0xFF450A0A),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(Icons.logout_rounded, color: Color(0xFFEF4444), size: 20),
            ),
            const SizedBox(width: 12),
            Text(
              'Keluar Sesi',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w900,
                color: textDark,
              ),
            ),
          ],
        ),
        content: Text(
          'Apakah Anda yakin ingin keluar dari portal Manager?',
          style: TextStyle(color: textMuted, fontSize: 13.5),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: Text(
              'Batal',
              style: TextStyle(
                color: textMuted,
                fontWeight: FontWeight.w700,
              ),
            ),
          ),
          InteractiveBouncingButton(
            borderRadius: BorderRadius.circular(12),
            onPressed: () {
              Navigator.pop(ctx);
              Navigator.of(context, rootNavigator: true).pushAndRemoveUntil(
                MaterialPageRoute(builder: (context) => const LoginScreen()),
                (route) => false,
              );
            },
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 10),
              decoration: BoxDecoration(
                color: const Color(0xFFEF4444),
                borderRadius: BorderRadius.circular(12),
              ),
              child: const Text(
                'Ya, Keluar',
                style: TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w800,
                  fontSize: 13,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final isDesktop = MediaQuery.of(context).size.width > 768;

    if (_isLoading) {
      return const Center(child: CircularProgressIndicator(color: Color(0xFF0284C7)));
    }

    final String nip = _getSafeString(['nip', 'employee_id', 'nik', 'id_anggota']);
    final String phone = _getSafeString(['phone_number', 'no_hp', 'phone', 'whatsapp']);
    final String department = _getSafeString(['department', 'departemen', 'divisi'], fallback: 'Teknologi Informasi');
    final String name = _getSafeString(['name', 'nama'], fallback: widget.userEmail.split('@').first);

    final textDark = isDark ? Colors.white : const Color(0xFF0F172A);
    final textMuted = isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B);
    final cardBg = isDark ? const Color(0xFF162238) : Colors.white;
    final borderColor = isDark ? const Color(0xFF233554) : const Color(0xFFE2E8F0);

    return SingleChildScrollView(
      physics: const BouncingScrollPhysics(),
      padding: EdgeInsets.symmetric(
        horizontal: isDesktop ? 36.0 : 20.0,
        vertical: 24.0,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildTopHeaderBar(isDark),
          _buildProfileHeroCard(isDark, name, department),
          const SizedBox(height: 24),
          Text(
            'Informasi Akun',
            style: TextStyle(
              fontSize: 17,
              fontWeight: FontWeight.w900,
              color: textDark,
              letterSpacing: -0.3,
            ),
          ),
          const SizedBox(height: 12),
          Container(
            decoration: BoxDecoration(
              color: cardBg,
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: borderColor, width: 1.2),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF64748B).withValues(alpha: 0.05),
                  blurRadius: 10,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Column(
              children: [
                _buildInfoTile(
                  icon: Icons.email_outlined,
                  title: 'Email Akun',
                  subtitle: widget.userEmail,
                  isDark: isDark,
                  textDark: textDark,
                  textMuted: textMuted,
                ),
                Divider(color: borderColor, height: 1),
                _buildInfoTile(
                  icon: Icons.badge_outlined,
                  title: 'NIP / ID Approver',
                  subtitle: nip,
                  isDark: isDark,
                  textDark: textDark,
                  textMuted: textMuted,
                ),
                Divider(color: borderColor, height: 1),
                _buildInfoTile(
                  icon: Icons.apartment_rounded,
                  title: 'Divisi / Departemen',
                  subtitle: department,
                  isDark: isDark,
                  textDark: textDark,
                  textMuted: textMuted,
                ),
                Divider(color: borderColor, height: 1),
                _buildInfoTile(
                  icon: Icons.phone_iphone_rounded,
                  title: 'No. WhatsApp / HP',
                  subtitle: phone,
                  isDark: isDark,
                  textDark: textDark,
                  textMuted: textMuted,
                ),
              ],
            ),
          ),
          const SizedBox(height: 22),
          Text(
            'Pengaturan & Tampilan',
            style: TextStyle(
              fontSize: 17,
              fontWeight: FontWeight.w900,
              color: textDark,
              letterSpacing: -0.3,
            ),
          ),
          const SizedBox(height: 12),
          Container(
            decoration: BoxDecoration(
              color: cardBg,
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: borderColor, width: 1.2),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF64748B).withValues(alpha: 0.05),
                  blurRadius: 10,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: ListTile(
              contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
              leading: Container(
                width: 40,
                height: 40,
                decoration: BoxDecoration(
                  color: isDark ? const Color(0xFF0C4A6E) : const Color(0xFFE0F2FE),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(
                  isDark ? Icons.dark_mode_rounded : Icons.light_mode_rounded,
                  color: const Color(0xFF0284C7),
                  size: 20,
                ),
              ),
              title: Text(
                'Mode Tampilan',
                style: TextStyle(
                  color: textDark,
                  fontSize: 14,
                  fontWeight: FontWeight.w800,
                ),
              ),
              subtitle: Text(
                isDark ? 'Tema Gelap (Dark Mode)' : 'Tema Terang (Light Mode)',
                style: TextStyle(color: textMuted, fontSize: 12),
              ),
              trailing: Switch(
                value: isDark,
                activeThumbColor: const Color(0xFF38BDF8),
                activeTrackColor: const Color(0xFF0284C7),
                onChanged: (val) {
                  themeModeNotifier.value = val ? ThemeMode.dark : ThemeMode.light;
                },
              ),
            ),
          ),
          const SizedBox(height: 26),
          SizedBox(
            width: double.infinity,
            child: InteractiveBouncingButton(
              borderRadius: BorderRadius.circular(16),
              onPressed: () => _showLogoutConfirmation(context, isDark),
              child: Container(
                padding: const EdgeInsets.symmetric(vertical: 16),
                decoration: BoxDecoration(
                  color: const Color(0xFFEF4444),
                  borderRadius: BorderRadius.circular(16),
                ),
                child: const Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.logout_rounded, color: Colors.white, size: 20),
                    SizedBox(width: 8),
                    Text(
                      'Keluar dari Sesi Manager',
                      style: TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w800,
                        fontSize: 14,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
          const SizedBox(height: 110),
        ],
      ),
    );
  }
}