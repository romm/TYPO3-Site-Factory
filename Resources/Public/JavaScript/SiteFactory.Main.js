// Declaring namespace.
window.SiteFactory = {};

if (!Object.keys) {
	Object.keys = function (obj) {
		var keys = [],
			k;
		for (k in obj) {
			if (Object.prototype.hasOwnProperty.call(obj, k)) {
				keys.push(k);
			}
		}
		return keys;
	};
}

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

jQuery(document).ready(function() {
	// Instantiating tooltips.
	jQuery('.factory-tooltip').tooltip({
		container: 'body'
	});
});