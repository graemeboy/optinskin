jQuery(document).ready(function($) {
	// Fade in when observed
	var oisCount = 0,
		fadeSec = 500,
		fadeSelf; // default
	var oisFadeInterval = setInterval(function() {
		$('.ois-fade').each(function() {
			oisCount++; // there is a fader
			fadeSelf = this;
			if ($(fadeSelf).is(":onScreen")) {
				// The design ought to appear.
				// Get the number of seconds before it should do.
				if ($(fadeSelf).attr('data-ois-fade-sec')) {
					fadeSec = parseInt($(this).attr('data-ois-fade-sec')) * 1000;
				} // if
				// Fade in after the given number of seconds.
				setTimeout(function() {
					$(fadeSelf).find('.ois-outer').fadeIn(1000, function() {
						$(fadeSelf).removeClass('ois-fade');
					});
				}, fadeSec);
				oisCount--; // remove this fader from our count
			} // if
			if (oisCount == 0) {
				// There are none left.
				clearInterval(oisFadeInterval);
			} // if	
		}); // each fader
		oisCount = 0; // reset for next check!
	}, // anon () 
	3000); // set Interval; every 3 seconds.
	var disable_submissions = ois.disable_submissions_stats;
	if (disable_submissions != 'yes') {
		$('.ois-design form').submit(function(e) {
			var oisService = $(this).attr('data-service');
			if (oisService != 'feedburner') {
				// Feedburner only has a popup
				e.preventDefault();
			} // if
			var selfId = $(this).attr('id');
			var self = this;
			var submissionData = "action=ois_ajax" + "&ois_submission_nonce=" + ois.ois_submission_nonce + "&postId=" + ois.postID + "&skinId=" + selfId + "&service=" + oisService;
			console.log(submissionData);
			$.ajax({
				type: "POST",
				url: ois.ajaxurl,
				data: submissionData,
				success: function(response) {
					if (oisService == 'feedburner') {
						if (data && data != 'no_redirect') {
							window.location.href = response;
						} // if
					} // if 
					else {
						$(self).unbind('submit'); // unbind
						$(self).submit(); // resubmit
					} // else
				},
				// success
			}); // ajax
		}); // submit ()
	} // if
}); // jQuery ()
// Add the "onScreen" attribute.
(function($) {
	$.expr[":"].onScreen = function(elem) {
		var $window = $(window);
		var viewport_top = $window.scrollTop();
		var viewport_height = $window.height();
		var viewport_bottom = viewport_top + viewport_height;
		var $elem = $(elem);
		var top = $elem.offset().top
		var height = $elem.height();
		var bottom = top + height
		return (top >= viewport_top && top < viewport_bottom) || (bottom > viewport_top && bottom <= viewport_bottom) || (height > viewport_height && top <= viewport_top && bottom >= viewport_bottom)
	}
})(jQuery);