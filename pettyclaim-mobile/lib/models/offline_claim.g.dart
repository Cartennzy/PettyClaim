// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'offline_claim.dart';

// **************************************************************************
// TypeAdapterGenerator
// **************************************************************************

class OfflineClaimAdapter extends TypeAdapter<OfflineClaim> {
  @override
  final int typeId = 0;

  @override
  OfflineClaim read(BinaryReader reader) {
    final numOfFields = reader.readByte();
    final fields = <int, dynamic>{
      for (int i = 0; i < numOfFields; i++) reader.readByte(): reader.read(),
    };
    return OfflineClaim(
      merchantName: fields[0] as String,
      amount: fields[1] as double,
      date: fields[2] as String,
      imagePath: fields[3] as String,
      status: fields[4] as String,
    );
  }

  @override
  void write(BinaryWriter writer, OfflineClaim obj) {
    writer
      ..writeByte(5)
      ..writeByte(0)
      ..write(obj.merchantName)
      ..writeByte(1)
      ..write(obj.amount)
      ..writeByte(2)
      ..write(obj.date)
      ..writeByte(3)
      ..write(obj.imagePath)
      ..writeByte(4)
      ..write(obj.status);
  }

  @override
  int get hashCode => typeId.hashCode;

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is OfflineClaimAdapter &&
          runtimeType == other.runtimeType &&
          typeId == other.typeId;
}
