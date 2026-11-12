import 'package:flutter/material.dart';

class NavbarKaryawan extends StatelessWidget {
  final int selectedIndex;
  final ValueChanged<int> onItemSelected;
  final VoidCallback onCenterButtonPressed;
  final bool isDark;

  const NavbarKaryawan({
    super.key,
    required this.selectedIndex,
    required this.onItemSelected,
    required this.onCenterButtonPressed,
    required this.isDark,
  });

  @override
  Widget build(BuildContext context) {
    final bgColor = isDark ? const Color(0xFF1E293B) : Colors.white;
    const primaryNavy = Color(0xFF0F2C59);
    const activeColor = primaryNavy;
    const inactiveColor = Color(0xFF94A3B8);

    return SafeArea(
      child: Container(
        height: 85,
        margin: const EdgeInsets.only(left: 20, right: 20, bottom: 16),
        child: Stack(
          alignment: Alignment.bottomCenter,
          clipBehavior: Clip.none,
          children: [
            // Bar Container Utama yang Mengapung
            Container(
              height: 65,
              decoration: BoxDecoration(
                color: bgColor,
                borderRadius: BorderRadius.circular(28),
                boxShadow: [
                  BoxShadow(
                    color: isDark
                        ? Colors.black.withValues(alpha: 0.5)
                        : const Color(0xFF0F2C59).withValues(alpha: 0.12),
                    blurRadius: 24,
                    spreadRadius: 0,
                    offset: const Offset(0, 8),
                  ),
                ],
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceAround,
                children: [
                  // 1. Home
                  _buildNavItem(
                    index: 0,
                    icon: Icons.home_rounded,
                    label: 'Home',
                    isSelected: selectedIndex == 0,
                    activeColor: activeColor,
                    inactiveColor: inactiveColor,
                  ),

                  // 2. Riwayat Klaim
                  _buildNavItem(
                    index: 1,
                    icon: Icons.receipt_long_rounded,
                    label: 'Riwayat',
                    isSelected: selectedIndex == 1,
                    activeColor: activeColor,
                    inactiveColor: inactiveColor,
                  ),

                  // Spacer untuk Tombol Floating Scan di tengah
                  const SizedBox(width: 52),

                  // 3. Keuangan (Wallet & Mutasi)
                  _buildNavItem(
                    index: 2,
                    icon: Icons.account_balance_wallet_rounded,
                    label: 'Keuangan',
                    isSelected: selectedIndex == 2,
                    activeColor: activeColor,
                    inactiveColor: inactiveColor,
                  ),

                  // 4. Profile
                  _buildNavItem(
                    index: 3,
                    icon: Icons.person_rounded,
                    label: 'Profile',
                    isSelected: selectedIndex == 3,
                    activeColor: activeColor,
                    inactiveColor: inactiveColor,
                  ),
                ],
              ),
            ),

            // Tombol Scan QR Tengah
            Positioned(
              top: 0,
              child: GestureDetector(
                onTap: onCenterButtonPressed,
                child: Container(
                  width: 56,
                  height: 56,
                  decoration: BoxDecoration(
                    color: primaryNavy,
                    shape: BoxShape.circle,
                    boxShadow: [
                      BoxShadow(
                        color: primaryNavy.withValues(alpha: 0.35),
                        blurRadius: 16,
                        offset: const Offset(0, 6),
                      ),
                    ],
                    border: Border.all(
                      color: isDark
                          ? const Color(0xFF0F172A)
                          : const Color(0xFFF8FAFC),
                      width: 4,
                    ),
                  ),
                  child: const Icon(
                    Icons.qr_code_scanner_rounded,
                    color: Colors.white,
                    size: 26,
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildNavItem({
    required int index,
    required IconData icon,
    required String label,
    required bool isSelected,
    required Color activeColor,
    required Color inactiveColor,
  }) {
    return InkWell(
      onTap: () => onItemSelected(index),
      borderRadius: BorderRadius.circular(20),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            AnimatedScale(
              duration: const Duration(milliseconds: 200),
              scale: isSelected ? 1.1 : 1.0,
              child: Icon(
                icon,
                color: isSelected ? activeColor : inactiveColor,
                size: 22,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              label,
              style: TextStyle(
                fontSize: 10,
                fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                color: isSelected ? activeColor : inactiveColor,
              ),
            ),
          ],
        ),
      ),
    );
  }
}