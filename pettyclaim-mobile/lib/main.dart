import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:hive_flutter/hive_flutter.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:device_preview/device_preview.dart';
import 'models/offline_claim.dart';
import 'screens/splash/splash_screen.dart';
import 'services/fcm_service.dart';

// Global Notifier untuk Switch Dark & Light Mode (Default: Light Mode)
final ValueNotifier<ThemeMode> themeModeNotifier = ValueNotifier(ThemeMode.light);

@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
}

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  if (!kIsWeb) {
    try {
      await Firebase.initializeApp();
      FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);
      await FCMService().initNotification();
    } catch (e) {
      debugPrint("Firebase Init Error: $e");
    }
  }

  await Hive.initFlutter();
  Hive.registerAdapter(OfflineClaimAdapter());
  await Hive.openBox<OfflineClaim>('claims_box');

  runApp(
    DevicePreview(
      enabled: !kReleaseMode,
      builder: (context) => const PettyClaimApp(),
    ),
  );
}

class PettyClaimApp extends StatelessWidget {
  const PettyClaimApp({super.key});

  @override
  Widget build(BuildContext context) {
    return ValueListenableBuilder<ThemeMode>(
      valueListenable: themeModeNotifier,
      builder: (context, currentMode, _) {
        return MaterialApp(
          locale: DevicePreview.locale(context),
          builder: DevicePreview.appBuilder,
          title: 'PettyClaim Mobile',
          debugShowCheckedModeBanner: false,
          themeMode: currentMode,
          theme: ThemeData(
            useMaterial3: true,
            brightness: Brightness.light,
            scaffoldBackgroundColor: const Color(0xFFF8FAFC),
            colorScheme: ColorScheme.fromSeed(
              seedColor: const Color(0xFF0F2C59),
              brightness: Brightness.light,
              surface: Colors.white,
              onSurface: const Color(0xFF0F172A),
            ),
            cardColor: Colors.white,
            dividerColor: const Color(0xFFE2E8F0),
            fontFamily: 'Plus Jakarta Sans',
          ),
          darkTheme: ThemeData(
            useMaterial3: true,
            brightness: Brightness.dark,
            scaffoldBackgroundColor: const Color(0xFF070B14),
            colorScheme: ColorScheme.fromSeed(
              seedColor: const Color(0xFF38BDF8),
              brightness: Brightness.dark,
              surface: const Color(0xFF0F172A),
              onSurface: const Color(0xFFF8FAFC),
            ),
            cardColor: const Color(0xFF0F172A),
            dividerColor: Colors.white10,
            fontFamily: 'Plus Jakarta Sans',
          ),
          home: const SplashScreen(),
        );
      },
    );
  }
}