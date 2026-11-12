import 'package:flutter/material.dart';

class InteractiveBouncingButton extends StatefulWidget {
  final Widget child;
  final VoidCallback? onPressed;
  final BorderRadius? borderRadius;
  final Color? splashColor;
  final Color? highlightColor;

  const InteractiveBouncingButton({
    super.key,
    required this.child,
    required this.onPressed,
    this.borderRadius,
    this.splashColor,
    this.highlightColor,
  });

  @override
  State<InteractiveBouncingButton> createState() => _InteractiveBouncingButtonState();
}

class _InteractiveBouncingButtonState extends State<InteractiveBouncingButton>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _scaleAnimation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 120),
    );
    _scaleAnimation = Tween<double>(begin: 1.0, end: 0.94).animate(
      CurvedAnimation(parent: _controller, curve: Curves.easeInOut),
    );
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _handleTapDown(TapDownDetails details) {
    if (widget.onPressed != null) {
      _controller.forward();
    }
  }

  void _handleTapUp(TapUpDetails details) {
    if (widget.onPressed != null) {
      _controller.reverse();
    }
  }

  void _handleTapCancel() {
    if (widget.onPressed != null) {
      _controller.reverse();
    }
  }

  @override
  Widget build(BuildContext context) {
    final radius = widget.borderRadius ?? BorderRadius.circular(16);

    return AnimatedBuilder(
      animation: _scaleAnimation,
      builder: (context, child) {
        return Transform.scale(
          scale: _scaleAnimation.value,
          child: Material(
            color: Colors.transparent,
            borderRadius: radius,
            child: InkWell(
              borderRadius: radius,
              splashColor: widget.splashColor ?? Colors.white.withValues(alpha: 0.25),
              highlightColor: widget.highlightColor ?? Colors.white.withValues(alpha: 0.12),
              onTapDown: _handleTapDown,
              onTapUp: _handleTapUp,
              onTapCancel: _handleTapCancel,
              onTap: widget.onPressed,
              child: widget.child,
            ),
          ),
        );
      },
    );
  }
}