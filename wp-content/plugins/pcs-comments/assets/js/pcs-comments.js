(function ($) {

    /**
     * Comment
     */
    function Comment(element) {
        this.element = element;
    }

    Comment.prototype = {
        getId: function () {
            let elementId = this.element.attr('id');
            return elementId.split('-')[1];
        },

        getMessage: function () {
            let commentId = this.getId();
            let commentContents = this.element.find(`#div-comment-${commentId}`);

            let paragraphs = commentContents.find('p').toArray();
            return paragraphs.map(paragraph => paragraph.innerText).join("\n");
        }
    };

    let deleteComment = function (commentId, action, nonce) {
        return $.ajax({
            url: window.ajaxurl,
            type: 'post',
            data: {
                'action': action,
                '_wp_nonce': nonce,
                'id': commentId
            }
        });
    };

    /**
     * Modal Window - Update Comment
     */
    function ModalWindowUpdateComment(element) {
        this.element = element;
    }

    ModalWindowUpdateComment.prototype = {
        getId: function () {
            let modalWindowCommentIdField = this.element.find('#comment-id');
            return modalWindowCommentIdField.val();
        },

        setId: function (commentId) {
            let modalWindowCommentIdField = this.element.find('#comment-id');
            modalWindowCommentIdField.val(commentId);
        },

        getMessage: function() {
            let modalWindowMessageField = this.element.find('#comment-message');
            return modalWindowMessageField.val();
        },

        setMessage: function (message) {
            let modalWindowMessageField = this.element.find('#comment-message');
            modalWindowMessageField.val(message);
        },

        update: function (comment) {
            let commentId = comment.getId();
            let commentMessage = comment.getMessage();

            this.setId(commentId)
            this.setMessage(commentMessage);
        }
    };

    function ModalWindowDeleteComment(element) {
        this.element = element;
    }

    ModalWindowDeleteComment.prototype = {
        getAction: function () {
            let modalWindowActionField = this.element.find('#delete-comment-action');
            return modalWindowActionField.val();
        },

        getId: function () {
            let modalWindowIdField = this.element.find('#delete-comment-id');
            return modalWindowIdField.val();
        },

        getNonce: function () {
            let modalWindowNonceField = this.element.find('#delete-comment-nonce');
            return modalWindowNonceField.val();
        },

        setId: function (commentId) {
            let modalWindowIdField = this.element.find('#delete-comment-id');
            modalWindowIdField.val(commentId);
        }
    };

    $(function () {
        // Update
        $('.btn-update-post-comment').on('click', function () {
            let updateCommentButton = $(this);

            let comment = new Comment(updateCommentButton.closest('.comment'));
            let modalWindowUpdateComment = new ModalWindowUpdateComment($('.update-comment-modal'));

            modalWindowUpdateComment.update(comment);
        });

        $('.btn-comment-save').on('click', function () {
            $('.update-comment-form').submit();
        });

        // Delete
        $('.btn-delete-post-comment').on('click', function () {
            let deleteCommentButton = $(this);

            let comment = new Comment(deleteCommentButton.closest('.comment'));
            let modalWindowDeleteComment = new ModalWindowDeleteComment($('.delete-comment-modal'));

            modalWindowDeleteComment.setId(comment.getId());
        });

        $('.btn-delete-comment-confirm').on('click', function (e) {
			let modalWindowDeleteComment = new ModalWindowDeleteComment($('.delete-comment-modal'));

            let commentId = modalWindowDeleteComment.getId();

            let promise = deleteComment(
                commentId,
                modalWindowDeleteComment.getAction(),
                modalWindowDeleteComment.getNonce()
            );

			promise.done(function (data) {
                if ( data.hasOwnProperty('status') && data.status === 'success' ) {
                    $(`#comment-${commentId}`).remove();
                    $(`#post-comment-${commentId}`).remove();
                }
			});
		});
    });

}(jQuery));
