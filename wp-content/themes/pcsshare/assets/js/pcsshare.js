(function ($) {
    var pcsshare = window.pcsshare;

    if (!pcsshare) {
        throw "Please include pcsshare objects";
    }

	function redirectToHomepage() {
        redirect(location.protocol + '//' + location.host);
	}

    function redirectAfterPostDelete() {
        var currentLocation = $('#pcs-current-location');
        if (currentLocation.val().length) {
            redirect(currentLocation.val());
        } else {
            redirectToHomepage();
        }
    }

    function redirect(newLocation) {
        window.location.href = newLocation;
    }

    function positionFooter() {
        var footer = $('.footer');

        footer.addClass('hidden');

        var d = jQuery(document);
        var w = jQuery(window);

        var documentHeight = d.height();
        var windowHeight = d.height();

        footer.removeClass('hidden');

        if (documentHeight >= windowHeight) {
            footer.offset({top: documentHeight - footer.height(), left: 0})
        }
    }

    $(function () {
        $('.nav-tabs').on('shown.bs.tab', 'li', function () {
            console.log('document height might have changed');
            positionFooter();
        });

        /**
         * Navigation slider
         */
		if ($('.slider').length) {
			var slider = new pcsshare.Slider('.slider');
			var sliderInterval = new pcsshare.SliderInterval(slider, 5000).start();

			$('.slider-arrow-prev').on('click', function (e) {
				slider.prev();
				sliderInterval.reset();
			});

			$('.slider-arrow-next').on('click', function (e) {
				slider.next();
				sliderInterval.reset();
			});

			$('.slider-navigation').on('click', 'label', function (e) {
				slider.activate($(this).index());
				sliderInterval.reset();
			});
		}

		/**
         * Category
         */
		// Redirect user to the post page after clicking the post box
		$('.post-excerpt').on('click', function (e) {
			if (e.target.tagName !== 'A' && $(e.target).closest('.watch-action').length === 0 &&
			    $(e.target).closest('.btn-update-post').length === 0 &&
				$(e.target).closest('.btn-delete-post').length === 0 &&
				$(e.target).closest('.btn-delete-comment').length === 0 &&
                $(e.target).closest('.btn-update-post-comment').length === 0) {
				e.preventDefault();
				var linkHolder = $(e.target).parentsUntil('.content-container').last();
				if ( ! linkHolder.length ) {
					linkHolder = $(e.target);
				}
				var url = linkHolder.data('link');
				window.location.href = url;
			}
		});

        /**
         * Comments
         */
        var commentsForm = $('#respond');
        var enterCommentButton = $('.enter-comment');

		var replying = ( window.location.href.indexOf('replytocom') > 0 ? true : false);

		if( false === replying && window.location.hash.indexOf('respond') < 0 ) {
			commentsForm.addClass('hidden');
		}

        enterCommentButton.on('click', function () {

            if ( true === replying ) {
                window.location.href = window.location.origin + window.location.pathname + '#respond';
                return;
            }

			commentsForm.toggleClass('hidden');

            if ( ! commentsForm.hasClass('hidden') ) {

                $('html, body').animate({
                    scrollTop: commentsForm.offset().top
                }, 2000);

            }

            positionFooter();

        });

        var commentsFormSubmitButton = $('#submit');

        if ( commentsFormSubmitButton.length && commentsFormSubmitButton.val() == 'Post Reply' ) {
            var cancelReplyButton = $('<input type="button" class="btn cancel-reply-btn" style="margin-left: 1em;" value="Cancel">');

            cancelReplyButton.on('click', function (e) {
                e.preventDefault();
                window.location.href = window.location.origin + window.location.pathname;
            });

            commentsFormSubmitButton.after(cancelReplyButton);
        }

        /**
         * New Post window
         */
        var postForm = $('.enter-post-form');

        if (postForm.length) {
            postForm.parsley().on('field:validated', function() {
                var ok = $('.parsley-error').length === 0;
                $('.bs-callout-warning').toggleClass('hidden', ok);
            });

            $('.btn-post-enter').on('click', function (e) {
                postForm.submit();
            });
        }

        var savePostThumbsUpClass = '.save-post-thumbs-up';
        var savePostThumbsDownClass = '.save-post-thumbs-down';

        $(savePostThumbsUpClass).on('click', function (e) {
            var button = $(this);

            button.toggleClass('lbg-style1');
            button.toggleClass('lbg-style1-active');

            $(savePostThumbsDownClass).addClass('unlbg-style1');
            $(savePostThumbsDownClass).removeClass('unlbg-style1-active');

            $('#post-vote').val('+1');
        });

        $(savePostThumbsDownClass).on('click', function (e) {
            var button = $(this);

            button.toggleClass('unlbg-style1');
            button.toggleClass('unlbg-style1-active');

            $(savePostThumbsUpClass).addClass('lbg-style1');
            $(savePostThumbsUpClass).removeClass('lbg-style1-active');

            $('#post-vote').val('-1');
        });

		/**
         * Update Post window
         */
		var updatePostForm = $('.update-post-form');
		var updatePostButton = $('.btn-update-post');

		var getPostData = function (container) {
			return {
				'title': container.find('.post-excerpt-title').text(),
				'link': container.find('.post-excerpt-link a').text(),
				'description': container.data('description'),
				'id': container.data('post-id')
			};
		};

		if (updatePostForm.length) {
            updatePostForm.parsley().on('field:validated', function() {
                var ok = $('.parsley-error').length === 0;
                $('.bs-callout-warning').toggleClass('hidden', ok);
            });

			updatePostButton.on('click', function (e) {
				var postData = getPostData($(e.target).closest('.user-post'));

				updatePostForm.find('#post-title').val(postData.title);
				updatePostForm.find('#post-link').val(postData.link);
				updatePostForm.find('#post-description').val(postData.description);
				updatePostForm.find('#post-id').val(postData.id);
			});

			$('.btn-post-save').on('click', function (e) {
                updatePostForm.submit();
            });
		}

		/**
         * Delete post
         */
		var deletePost = function (postId, nonce, action) {
			return $.ajax({
				url: window.ajaxurl,
				type: 'post',
				data: {
					'action': action,
					'_wpnonce': nonce,
					'id': postId
				}
			});
		};

        var deletPostConfirmButton = $('.btn-delete-post-confirm');

        $('.btn-delete-post').on('click', function (e) {
            var button = $(this);

            deletPostConfirmButton.attr('data-post-id', $(this).data('post-id'));
            deletPostConfirmButton.attr('data-wp-nonce', $(this).data('wp-nonce'));
            deletPostConfirmButton.attr('data-action', $(this).data('action'));
        });

        deletPostConfirmButton.on('click', function (e) {
            var button = $(this);

            var promise = deletePost(
                button.data('post-id'),
                button.data('wp-nonce'),
                button.data('action')
            );

            promise.done(function (data) {
                if ( data && data.status === 'success' ) {
					redirectAfterPostDelete();
				}
            });
        });

		/**
         * Delete comment
         */
		var deleteComment = function (commentId, nonce) {
			return $.ajax({
				url: window.ajaxurl,
				type: 'post',
				data: {
					'action': 'pcs-delete-comment',
					'_wp_nonce': nonce,
					'id': commentId
				}
			});
		};

		$('.btn-delete-comment').on('click', function (e) {
			var button = $(this);
			var comment = button.closest('.post-excerpt');

            $('.delete-comment-modal').find('#delete-comment-id').val(button.data('comment-id'));
		});

        /**
         * Update user profile
         */
        var updateProfileForm = $('.update-profile-form');
 		var updateProfileButton = $('.btn-edit-profile');

 		var getProfileData = function (container) {;
 			return {
 				'dateOfInjury': $('.profile-info-value-date-of-injury').text(),
 				'causeOfInjury': $('.profile-info-value-cause-of-injury').text(),
 				'symptoms': $('.profile-info-value-symptoms').text(),
 				'additionalInformation': $('.profile-info-value-additional-information').text(),
                'id': container.data('profile-id')
 			};
 		};

 		var setProfileData = function (data) {
            $('.profile-info-value-date-of-injury').text(
                moment(data.dateOfInjury).format('MMMM DD, YYYY')
            );
            $('.profile-info-value-cause-of-injury').text(data.causeOfInjury);
            $('.profile-info-value-symptoms').text(data.symptoms);
            $('.profile-info-value-additional-information').html(
                convertNewlines(data.additionalInformation)
            );
        };

        var convertNewlines = function (text) {
            var domText = $(text);
            var cleanText = domText.length ? domText.text() : text;
            return cleanText.replace(/\n/g, '<br>');
        };

        var getProfileFormData = function (target) {
            return {
                'dateOfInjury': updateProfileForm.find('#date-of-injury').val(),
                'causeOfInjury': updateProfileForm.find('#cause-of-injury').val(),
                'symptoms': updateProfileForm.find('#symptoms').val(),
                'additionalInformation': updateProfileForm.find('#additional-information').val(),
                '_wp_nonce': updateProfileForm.find('#profile-nonce').val()
            };
        };

 		var updateProfile = function (profileId, data) {
 			return $.ajax({
 				url: window.ajaxurl,
 				type: 'post',
 				data: {
 					'action': 'pcs-update-profile',
 					'_wp_nonce': data._wp_nonce,
 					'id': profileId,
 					'dateOfInjury': data.dateOfInjury,
 					'causeOfInjury': data.causeOfInjury,
 					'symptoms': data.symptoms,
                    'additionalInformation': data.additionalInformation
 				}
 			});
 		}

 		if (updateProfileForm.length) {
             updateProfileButton.on('click', function (e) {
 				var profileData = getProfileData($(this));

                updateProfileForm.find('#profile-id').val(profileData.id);
                updateProfileForm.find('#date-of-injury').val(moment(profileData.dateOfInjury, 'MMMM DD, YYYY').format('YYYY-MM-DD'));
                updateProfileForm.find('#cause-of-injury').val(profileData.causeOfInjury);
                updateProfileForm.find('#symptoms').val(profileData.symptoms);
                updateProfileForm.find('#additional-information').val(profileData.additionalInformation);
 			});

 			$('.btn-profile-save').on('click', function (e) {
                updateProfileForm.submit();
             });
 		}

		/**
         * Delete user profile
         */
		var deleteProfile = function (profileId, nonce) {
			return $.ajax({
				url: window.ajaxurl,
				type: 'post',
                dataType: 'json',
				data: {
					'action': 'pcs-delete-profile',
					'_wp_nonce': nonce,
					'id': profileId
				}
			});
		};

        var deleteProfileConfirmButton = $('.btn-profile-delete-confirm');

        $('.btn-delete-profile').on('click', function (e) {
            var button = $(this);
            deleteProfileConfirmButton.attr('data-profile-id', button.data('profile-id'));
            deleteProfileConfirmButton.attr('data-wp-nonce', button.data('wp-nonce'));
        });

        deleteProfileConfirmButton.on('click', function (e) {
			var button = $(this);
			var promise = deleteProfile(button.data('profile-id'), button.data('wp-nonce'));

			promise.done(function (data) {
				if ( data && data.status === 'success' ) {
					redirectToHomepage();
				}
			});
		});

		/**
         * Display usernames of people who liked/disliked a post inside popover
         */
		var peopleWhoLiked = $('.people-who-liked'),
			peopleWhoUnliked = $('.people-who-disliked');

		$('.watch-action').each(function (index, watchActionDOMElement) {
			var watchActionElement = $(watchActionDOMElement),
				actionLike = watchActionElement.find('.action-like'),
				actionUnlike = watchActionElement.find('.action-unlike');

			// Handle like
			var actionLikePopover = actionLike.popover({
				'html': true,
				'trigger': 'manual',
				'placement': 'bottom',
				'title': 'Effective',
				'content': function () {
					var peopleList = peopleWhoLiked.eq(index);
					return peopleList.find('li').length ? peopleList.html() : 'No-one :(';
				}
			});

			actionLike.on('mouseenter', function () {
				if ($('.popover').css('display') !== 'block') {
					actionLikePopover.popover('show');
				}
			});

			// Handle unlike
			var actionDislikePopver = actionUnlike.popover({
				'html': true,
				'trigger': 'manual',
				'placement': 'bottom',
				'title': 'Ineffective',
				'content': function () {
					var peopleList = peopleWhoUnliked.eq(index);
					return peopleList.find('li').length ? peopleList.html() : 'No-one :)';
				}
			});

			actionUnlike.on('mouseenter', function () {
				if ($('.popover').css('display') !== 'block') {
					actionDislikePopver.popover('show');
				}
			});

			// Handle closing
			watchActionElement.on('mouseleave', function () {
				actionLikePopover.popover('hide');
				actionDislikePopver.popover('hide');
			});
		});

        /**
         * Cancel thumbs up/thumbs down
         */
        $(document).on('click', 'a[class*=-active]', function (e) {
            e.stopImmediatePropagation();

            var button = $(this);
            var postId = button.data('post_id');

            var voteToUse = null;

            if (button.hasClass('like-' + postId)) {
                voteToUse = 'unlike';
            } else if (button.hasClass('unlike-' + postId)) {
                voteToUse = 'like';
            }

            if (voteToUse !== null) {
                var parentContainer = button.parentsUntil('.watch-action');
                var oppositeActionButton = parentContainer.find('.action-' + voteToUse).find('.jlk');
                console.log(oppositeActionButton);
                oppositeActionButton[0].click();
            }
        });

        positionFooter();
    });
}(jQuery));
