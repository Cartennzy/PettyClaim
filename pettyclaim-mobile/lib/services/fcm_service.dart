import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:flutter/foundation.dart';
import 'api_service.dart';

class FCMService {
  final FirebaseMessaging _fcm = FirebaseMessaging.instance;
  final FlutterLocalNotificationsPlugin _flutterLocalNotificationsPlugin =
      FlutterLocalNotificationsPlugin();

  Future<void> initNotification({String? userToken}) async {
    // 1. Request izin notifikasi dari HP
    await _fcm.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );

    // 2. Ambil FCM Token unik HP
    String? fcmToken = await _fcm.getToken();
    if (fcmToken != null) {
      debugPrint("FCM Device Token: $fcmToken");
      await ApiService.saveFcmToken(fcmToken, token: userToken);
    }

    // Listener jika token FCM berubah/diperbarui
    _fcm.onTokenRefresh.listen((newToken) async {
      await ApiService.saveFcmToken(newToken, token: userToken);
    });

    // 3. Konfigurasi Notifikasi Pop-up Lokal (Foreground)
    const AndroidInitializationSettings androidSettings =
        AndroidInitializationSettings('@mipmap/ic_launcher');
    const InitializationSettings initSettings =
        InitializationSettings(android: androidSettings);

    await _flutterLocalNotificationsPlugin.initialize(initSettings);

    // 4. Handle pesan masuk saat aplikasi sedang dibuka
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      RemoteNotification? notification = message.notification;
      AndroidNotification? android = message.notification?.android;

      if (notification != null && android != null) {
        _flutterLocalNotificationsPlugin.show(
          notification.hashCode,
          notification.title,
          notification.body,
          const NotificationDetails(
            android: AndroidNotificationDetails(
              'petty_claim_channel',
              'PettyClaim Push Channel',
              importance: Importance.max,
              priority: Priority.high,
              icon: '@mipmap/ic_launcher',
            ),
          ),
        );
      }
    });
  }
}