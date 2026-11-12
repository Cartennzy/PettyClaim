import 'package:hive/hive.dart';

part 'offline_claim.g.dart';

@HiveType(typeId: 0)
class OfflineClaim extends HiveObject {
  @HiveField(0)
  final String merchantName;

  @HiveField(1)
  final double amount;

  @HiveField(2)
  final String date;

  @HiveField(3)
  final String imagePath;

  @HiveField(4)
  final String status;

  OfflineClaim({
    required this.merchantName,
    required this.amount,
    required this.date,
    required this.imagePath,
    this.status = 'Pending',
  });
}