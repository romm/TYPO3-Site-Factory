/*
 * Conflict between jQuery and prototype: we need to overload the following
 * function to get the Bootstrap's tooltip plugin work fine.
 */
jQuery.fn.tooltip.Constructor.prototype.hide = function () {
	var that = this;
	var jQuerytip = this.tip();
	var e = jQuery.Event('hide.bs.' + this.type);
	this.$element.removeAttr('aria-describedby');
	function complete() {
		if (that.hoverState != 'in') jQuerytip.detach();
		that.$element.trigger('hidden.bs.' + that.type);
	}
	this.$element.triggerHandler(e); // Here's the modification: "triggerHandler" instead of "trigger"
	if (e.isDefaultPrevented()) return;
	jQuerytip.removeClass('in');
	jQuery.support.transition && this.$tip.hasClass('fade') ?
		jQuerytip
			.one('bsTransitionEnd', complete)
			.emulateTransitionEnd(150) :
		complete();
	this.hoverState = null;
	return this;
};