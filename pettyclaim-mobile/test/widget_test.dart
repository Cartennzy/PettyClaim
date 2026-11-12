import 'package:flutter_test/flutter_test.dart';
import 'package:pettyclaim_mobile/main.dart';

void main() {
  testWidgets('PettyClaimApp UI smoke test', (WidgetTester tester) async {
    // Build aplikasi PettyClaimApp dan trigger satu frame.
    await tester.pumpWidget(const PettyClaimApp());

    // Memastikan judul halaman pilihan role muncul di layar
    expect(find.text('PettyClaim - Pilih Role App'), findsOneWidget);
    
    // Memastikan tombol Role Karyawan dan Role Manager muncul
    expect(find.text('Role Karyawan (Scan Struk)'), findsOneWidget);
    expect(find.text('Role Manager / HRD (Approval)'), findsOneWidget);
  });
}