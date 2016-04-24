// Declaring SiteFactory namespace.
window.SiteFactory = {
	ajaxUrl: ''
};

// Instantiating tooltips.
jQuery(document).ready(function() {
	jQuery('.factory-tooltip').tooltip({
		container: 'body'
	});
});

/**
 * Converts an integer timer to a human-readable string.
 * Example: "100" will be converted to "01:40".
 *
 * @returns	{string}	The converted date.
 */
String.prototype.toHHMMSS = function () {
	var sec_num	= parseInt(this, 10);
	var hours	= Math.floor(sec_num / 3600);
	var minutes	= Math.floor((sec_num - (hours * 3600)) / 60);
	var seconds	= sec_num - (hours * 3600) - (minutes * 60);

	if (hours   < 10) {hours   = '0' + hours;}
	if (minutes < 10) {minutes = '0' + minutes;}
	if (seconds < 10) {seconds = '0' + seconds;}

	var time = '';
	if (hours != 0) time += hours + ':';
	time += minutes + ':' + seconds;

	return time;
};

/**
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