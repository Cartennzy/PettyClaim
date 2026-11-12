import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import '../../main.dart';
import '../../services/api_service.dart';
import '../../widgets/components/interactive_bouncing_button.dart';
import '../auth/login_screen.dart';

class KaryawanProfileScreen extends StatefulWidget {
  final String userEmail;
  final int totalClaimsCount;

  const KaryawanProfileScreen({
    super.key,
    required this.userEmail,
    this.totalClaimsCount = 0,
  });

  @override
  State<KaryawanProfileScreen> createState() => _KaryawanProfileScreenState();
}

class _KaryawanProfileScreenState extends State<KaryawanProfileScreen> {
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

  String _formatDepartmentShort(String dept) {
    final lower = dept.toLowerCase().trim();
    if (lower.contains('teknologi informasi') || lower.contains('information technology') || lower == 'it') {
      return 'IT';
    } else if (lower.contains('human resource') || lower.contains('sdm') || lower.contains('hrd') || lower == 'hr') {
      return 'HRD';
    } else if (lower.contains('keuangan') || lower.contains('finance') || lower.contains('accounting')) {
      return 'FIN';
    } else if (lower.contains('operasional') || lower.contains('operations') || lower == 'ops') {
      return 'OPS';
    } else if (lower.contains('marketing') || lower.contains('pemasaran')) {
      return 'MKT';
    } else if (lower.contains('logistik') || lower.contains('logistics')) {
      return 'LOG';
    } else if (lower.contains('umum') || lower.contains('general affair') || lower == 'ga') {
      return 'GA';
    } else if (lower.contains('legal') || lower.contains('hukum')) {
      return 'LGL';
    }

    if (dept.length > 10 && dept.contains(' ')) {
      return dept
          .split(' ')
          .where((w) => w.isNotEmpty)
          .map((w) => w[0].toUpperCase())
          .take(4)
          .join();
    }
    return dept;
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
                'Profil & Akun',
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
    final initialLetter = name.isNotEmpty ? name[0].toUpperCase() : 'K';
    final shortDept = _formatDepartmentShort(department);

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
            'Karyawan Pemohon • $department',
            textAlign: TextAlign.center,
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
                Expanded(child: _buildStatItem('Klaim Diajukan', '${widget.totalClaimsCount}')),
                _buildStatDivider(),
                Expanded(child: _buildStatItem('Departemen', shortDept)),
                _buildStatDivider(),
                Expanded(child: _buildStatItem('Status', 'Aktif')),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatItem(String label, String value) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Text(
          value,
          textAlign: TextAlign.center,
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
          style: const TextStyle(
            color: Colors.white,
            fontSize: 18,
            fontWeight: FontWeight.w900,
          ),
        ),
        const SizedBox(height: 2),
        Text(
          label,
          textAlign: TextAlign.center,
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
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

  void _showChangePasswordModal(BuildContext parentContext, bool isDark) {
    final formKey = GlobalKey<FormState>();
    final oldPasswordController = TextEditingController();
    final newPasswordController = TextEditingController();
    final confirmPasswordController = TextEditingController();

    bool obscureOld = true;
    bool obscureNew = true;
    bool obscureConfirm = true;
    bool isSubmitting = false;

    showModalBottomSheet(
      context: parentContext,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (modalContext) {
        return StatefulBuilder(
          builder: (modalContext, setModalState) {
            return Container(
              padding: EdgeInsets.only(
                left: 24,
                right: 24,
                top: 24,
                bottom: MediaQuery.of(modalContext).viewInsets.bottom + 24,
              ),
              decoration: BoxDecoration(
                color: isDark ? const Color(0xFF162238) : Colors.white,
                borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
                border: Border.all(color: isDark ? const Color(0xFF233554) : const Color(0xFFE2E8F0)),
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
                        Text(
                          'Ganti Password Akun',
                          style: TextStyle(
                            color: isDark ? Colors.white : const Color(0xFF0F172A),
                            fontSize: 18,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                        IconButton(
                          icon: Icon(Icons.close, color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B)),
                          onPressed: () => Navigator.pop(modalContext),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: oldPasswordController,
                      obscureText: obscureOld,
                      style: TextStyle(color: isDark ? Colors.white : const Color(0xFF0F172A)),
                      validator: (value) => (value == null || value.trim().isEmpty) ? 'Password saat ini wajib diisi' : null,
                      decoration: InputDecoration(
                        labelText: 'Password Saat Ini',
                        prefixIcon: const Icon(Icons.lock_outline, color: Color(0xFF0284C7)),
                        suffixIcon: IconButton(
                          icon: Icon(obscureOld ? Icons.visibility_off : Icons.visibility),
                          onPressed: () => setModalState(() => obscureOld = !obscureOld),
                        ),
                        filled: true,
                        fillColor: isDark ? const Color(0xFF0A1120) : const Color(0xFFF8FAFC),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: BorderSide.none),
                      ),
                    ),
                    const SizedBox(height: 12),
                    TextFormField(
                      controller: newPasswordController,
                      obscureText: obscureNew,
                      style: TextStyle(color: isDark ? Colors.white : const Color(0xFF0F172A)),
                      validator: (value) {
                        if (value == null || value.trim().isEmpty) return 'Password baru wajib diisi';
                        if (value.length < 6) return 'Password baru minimal 6 karakter';
                        return null;
                      },
                      decoration: InputDecoration(
                        labelText: 'Password Baru',
                        prefixIcon: const Icon(Icons.key_outlined, color: Color(0xFF0284C7)),
                        suffixIcon: IconButton(
                          icon: Icon(obscureNew ? Icons.visibility_off : Icons.visibility),
                          onPressed: () => setModalState(() => obscureNew = !obscureNew),
                        ),
                        filled: true,
                        fillColor: isDark ? const Color(0xFF0A1120) : const Color(0xFFF8FAFC),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: BorderSide.none),
                      ),
                    ),
                    const SizedBox(height: 12),
                    TextFormField(
                      controller: confirmPasswordController,
                      obscureText: obscureConfirm,
                      style: TextStyle(color: isDark ? Colors.white : const Color(0xFF0F172A)),
                      validator: (value) {
                        if (value != newPasswordController.text) return 'Konfirmasi password tidak cocok';
                        return null;
                      },
                      decoration: InputDecoration(
                        labelText: 'Konfirmasi Password Baru',
                        prefixIcon: const Icon(Icons.check_circle_outline, color: Color(0xFF0284C7)),
                        suffixIcon: IconButton(
                          icon: Icon(obscureConfirm ? Icons.visibility_off : Icons.visibility),
                          onPressed: () => setModalState(() => obscureConfirm = !obscureConfirm),
                        ),
                        filled: true,
                        fillColor: isDark ? const Color(0xFF0A1120) : const Color(0xFFF8FAFC),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: BorderSide.none),
                      ),
                    ),
                    const SizedBox(height: 20),
                    SizedBox(
                      width: double.infinity,
                      child: InteractiveBouncingButton(
                        borderRadius: BorderRadius.circular(14),
                        onPressed: isSubmitting
                            ? null
                            : () async {
                                if (!formKey.currentState!.validate()) return;
                                setModalState(() => isSubmitting = true);

                                final nav = Navigator.of(modalContext);
                                final msg = ScaffoldMessenger.of(parentContext);

                                try {
                                  final response = await http.post(
                                    Uri.parse('${ApiService.baseUrl}/change-password'),
                                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
                                    body: jsonEncode({
                                      'email': widget.userEmail,
                                      'old_password': oldPasswordController.text,
                                      'new_password': newPasswordController.text,
                                    }),
                                  );
                                  final data = jsonDecode(response.body);
                                  if (!modalContext.mounted) return;
                                  setModalState(() => isSubmitting = false);

                                  if (response.statusCode == 200 && data['success'] == true) {
                                    nav.pop();
                                    msg.showSnackBar(
                                      const SnackBar(
                                        backgroundColor: Color(0xFF10B981),
                                        content: Text('Password berhasil diperbarui!'),
                                      ),
                                    );
                                  } else {
                                    msg.showSnackBar(
                                      SnackBar(
                                        backgroundColor: const Color(0xFFEF4444),
                                        content: Text(data['message'] ?? 'Gagal mengubah password.'),
                                      ),
                                    );
                                  }
                                } catch (e) {
                                  if (!modalContext.mounted) return;
                                  setModalState(() => isSubmitting = false);
                                  msg.showSnackBar(
                                    const SnackBar(
                                      backgroundColor: Color(0xFFEF4444),
                                      content: Text('Gagal terhubung ke server.'),
                                    ),
                                  );
                                }
                              },
                        child: Container(
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          decoration: BoxDecoration(
                            color: const Color(0xFF0284C7),
                            borderRadius: BorderRadius.circular(14),
                          ),
                          child: Center(
                            child: isSubmitting
                                ? const SizedBox(
                                    width: 18,
                                    height: 18,
                                    child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                                  )
                                : const Text(
                                    'Simpan Password Baru',
                                    style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 13.5),
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
          'Apakah Anda yakin ingin keluar dari portal Karyawan?',
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

    // Eksekusi Pengecekan Multi-Key agar cocok dengan penamaan backend admin Anda
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
                  title: 'NIP / ID Anggota',
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
            child: Column(
              children: [
                ListTile(
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
                Divider(color: borderColor, height: 1),
                ListTile(
                  onTap: () => _showChangePasswordModal(context, isDark),
                  contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
                  leading: Container(
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      color: isDark ? const Color(0xFF0C4A6E) : const Color(0xFFE0F2FE),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(
                      Icons.lock_reset_rounded,
                      color: Color(0xFF0284C7),
                      size: 20,
                    ),
                  ),
                  title: Text(
                    'Ganti Password',
                    style: TextStyle(
                      color: textDark,
                      fontSize: 14,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                  subtitle: Text(
                    'Perbarui kata sandi akun',
                    style: TextStyle(color: textMuted, fontSize: 12),
                  ),
                  trailing: Icon(Icons.arrow_forward_ios_rounded, size: 14, color: textMuted),
                ),
              ],
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
                      'Keluar dari Sesi Karyawan',
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