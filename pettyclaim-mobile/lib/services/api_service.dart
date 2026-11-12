import 'dart:async';
import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;

class ApiService {
  static String get baseUrl {
    if (kIsWeb) {
      return 'http://localhost:8000/api';
    } else {
      return 'https://appliance-haunt-shifty.ngrok-free.dev/api';
    }
  }

  static Map<String, String> _getHeaders({String? token}) {
    final headers = <String, String>{
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'ngrok-skip-browser-warning': 'true',
    };
    if (token != null && token.isNotEmpty) {
      headers['Authorization'] = 'Bearer $token';
    }
    return headers;
  }

  static Future fetchUserProfile(String email, {String? token}) async {
    try {
      final headers = _getHeaders(token: token);
      final timestamp = DateTime.now().millisecondsSinceEpoch;

      final response = await http.get(
        Uri.parse('$baseUrl/profile?email=$email&_t=$timestamp'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        final body = jsonDecode(response.body);
        if (body['success'] == true) {
          return body['data'];
        }
      }
      return null;
    } catch (e) {
      debugPrint('Error Fetch Profile: $e');
      return null;
    }
  }

  static Future processReceiptScan({
    required Uint8List imageBytes,
    String? filename,
    String? rawText,
    String? token,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/claims/process-scan');
      final request = http.MultipartRequest('POST', uri)
        ..headers['Accept'] = 'application/json'
        ..headers['ngrok-skip-browser-warning'] = 'true';

      if (token != null && token.isNotEmpty) {
        request.headers['Authorization'] = 'Bearer $token';
      }

      request.files.add(
        http.MultipartFile.fromBytes(
          'receipt_image',
          imageBytes,
          filename: filename ?? 'receipt.jpg',
        ),
      );

      if (rawText != null && rawText.isNotEmpty) {
        request.fields['raw_text'] = rawText;
      }

      final streamedResponse = await request.send().timeout(const Duration(seconds: 40));
      final response = await http.Response.fromStream(streamedResponse);

      if (response.statusCode == 200) {
        final body = jsonDecode(response.body);
        if (body['success'] == true) {
          return body['data'];
        }
      } else {
        debugPrint('Process Scan Failed with Status: ${response.statusCode}');
        debugPrint('Response Body: ${response.body}');
      }
      return null;
    } catch (e) {
      debugPrint('Error Process Receipt Scan: $e');
      return null;
    }
  }

  static Future<bool> submitClaim({
    required String merchantName,
    required double amount,
    required String date,
    required Uint8List imageBytes,
    String? filename,
    String? notes,
    String? token,
    String? employeeEmail,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/claims');
      final request = http.MultipartRequest('POST', uri)
        ..headers['Accept'] = 'application/json'
        ..headers['ngrok-skip-browser-warning'] = 'true'
        ..fields['merchant_name'] = merchantName
        ..fields['amount'] = amount.toString()
        ..fields['transaction_date'] = date
        ..fields['notes'] = notes ?? 'Pengajuan via Scan Struk'
        ..fields['employee_email'] = employeeEmail ?? ''
        ..files.add(
          http.MultipartFile.fromBytes(
            'receipt_image',
            imageBytes,
            filename: filename ?? 'receipt.jpg',
          ),
        );

      if (token != null && token.isNotEmpty) {
        request.headers['Authorization'] = 'Bearer $token';
      }

      final streamedResponse = await request.send().timeout(const Duration(seconds: 40));
      final response = await http.Response.fromStream(streamedResponse);

      return response.statusCode == 201 || response.statusCode == 200;
    } catch (e) {
      debugPrint('Error Submit Claim: $e');
      return false;
    }
  }

  static Future<List<dynamic>> fetchClaims({String? email, String? token}) async {
    try {
      final headers = _getHeaders(token: token);
      final timestamp = DateTime.now().millisecondsSinceEpoch;

      final url = (email != null && email.isNotEmpty)
          ? '$baseUrl/claims?email=$email&_t=$timestamp'
          : '$baseUrl/claims?_t=$timestamp';

      final response = await http.get(Uri.parse(url), headers: headers);

      if (response.statusCode == 200) {
        final body = jsonDecode(response.body);
        if (body['success'] == true && body['data'] != null) {
          return body['data'] is List ? body['data'] : [body['data']];
        } else if (body is List) {
          return body;
        }
      }
      return [];
    } catch (e) {
      debugPrint('Error Fetch Claims: $e');
      return [];
    }
  }

  static Future<List<dynamic>> fetchManagerClaims({String? managerEmail, String? token}) async {
    try {
      final headers = _getHeaders(token: token);
      final timestamp = DateTime.now().millisecondsSinceEpoch;

      final url = (managerEmail != null && managerEmail.isNotEmpty)
          ? '$baseUrl/manager/claims?email=$managerEmail&_t=$timestamp'
          : '$baseUrl/manager/claims?_t=$timestamp';

      final response = await http.get(Uri.parse(url), headers: headers);

      if (response.statusCode == 200) {
        final body = jsonDecode(response.body);
        if (body['success'] == true && body['data'] != null) {
          return body['data'] is List ? body['data'] : [body['data']];
        }
      }
      return [];
    } catch (e) {
      debugPrint('Error Fetch Manager Claims: $e');
      return [];
    }
  }

  static Future fetchClaimDataWithSummary({String? email, String? token}) async {
    try {
      final headers = _getHeaders(token: token);
      final timestamp = DateTime.now().millisecondsSinceEpoch;

      final url = (email != null && email.isNotEmpty)
          ? '$baseUrl/claims?email=$email&_t=$timestamp'
          : '$baseUrl/claims?_t=$timestamp';

      final response = await http.get(Uri.parse(url), headers: headers);

      if (response.statusCode == 200) {
        final body = jsonDecode(response.body);
        if (body['success'] == true) {
          return {
            'claims': body['data'] is List ? body['data'] : [body['data']],
            'limit_summary': body['limit_summary'] ?? {
              'monthly_limit': 5000000.0,
              'used_amount': 0.0,
              'remaining_limit': 5000000.0,
            },
          };
        }
      }
      return {'claims': [], 'limit_summary': null};
    } catch (e) {
      debugPrint('Error Fetch Claim Data With Summary: $e');
      return {'claims': [], 'limit_summary': null};
    }
  }

  static Future fetchFinanceSummary(String email, {String? token}) async {
    try {
      final headers = _getHeaders(token: token);
      final timestamp = DateTime.now().millisecondsSinceEpoch;

      final response = await http.get(
        Uri.parse('$baseUrl/wallet/summary?email=$email&_t=$timestamp'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        final body = jsonDecode(response.body);
        if (body['success'] == true) {
          return body['data'];
        }
      }
      return null;
    } catch (e) {
      debugPrint('Error Fetch Finance Summary: $e');
      return null;
    }
  }

  static Future<bool> syncApproveClaim({
    String? claimId,
    required String employeeEmail,
    required String merchantName,
    required double amount,
    required String status,
    String? notes,
    String? receiptUrl,
    String? token,
  }) async {
    try {
      final headers = _getHeaders(token: token);

      if (claimId != null && claimId.isNotEmpty) {
        final response = await http.post(
          Uri.parse('$baseUrl/claims/$claimId/approve'),
          headers: headers,
          body: jsonEncode({
            'status': status.toUpperCase(),
            'notes': notes ?? '',
          }),
        );
        if (response.statusCode == 200 || response.statusCode == 201) {
          return true;
        }
      }

      final response = await http.post(
        Uri.parse('$baseUrl/claims/approve'),
        headers: headers,
        body: jsonEncode({
          'employee_email': employeeEmail,
          'merchant_name': merchantName,
          'amount': amount,
          'status': status.toUpperCase(),
          'notes': notes ?? '',
          'receipt_url': receiptUrl ?? '',
        }),
      );

      return response.statusCode == 200 || response.statusCode == 201;
    } catch (e) {
      debugPrint('Error Sync Approve: $e');
      return false;
    }
  }

  static Future<bool> saveFcmToken(String fcmToken, {String? token}) async {
    try {
      final headers = _getHeaders(token: token);

      final response = await http.post(
        Uri.parse('$baseUrl/user/fcm-token'),
        headers: headers,
        body: jsonEncode({'fcm_token': fcmToken}),
      );

      return response.statusCode == 200;
    } catch (e) {
      debugPrint('Error Save FCM Token: $e');
      return false;
    }
  }

  static Future withdrawWallet({
    required double amount,
    required String channel,
    required String accountNumber,
    required String accountName,
    required String email,
    String? token,
  }) async {
    try {
      final headers = _getHeaders(token: token);

      final response = await http.post(
        Uri.parse('$baseUrl/wallet/withdraw'),
        headers: headers,
        body: jsonEncode({
          'amount': amount,
          'channel': channel,
          'account_number': accountNumber,
          'account_name': accountName,
          'email': email,
        }),
      );

      if (response.statusCode == 200 || response.statusCode == 201) {
        return jsonDecode(response.body);
      } else {
        final body = jsonDecode(response.body);
        return {
          'success': false,
          'message': body['message'] ?? 'Gagal memproses penarikan dana.',
        };
      }
    } catch (e) {
      debugPrint('Error Withdraw Wallet: $e');
      return {
        'success': false,
        'message': 'Gagal terhubung ke server backend.',
      };
    }
  }

  static Future<double?> getLimitKas({String? token}) async {
    try {
      final headers = _getHeaders(token: token);
      final timestamp = DateTime.now().millisecondsSinceEpoch;
      final response = await http.get(
        Uri.parse('$baseUrl/limit-kas?_t=$timestamp'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        final body = jsonDecode(response.body);
        
        if (body['success'] == true) {
            if (body['remaining_limit'] != null) {
                return (body['remaining_limit'] as num).toDouble();
            }
            return (body['limit'] as num?)?.toDouble();
        } else if (body['limit'] != null) {
            return (body['limit'] as num).toDouble();
        }
      }
      return null;
    } catch (e) {
      debugPrint('Error getLimitKas: $e');
      return null;
    }
  }
}