(function ($) {

    function Post(container) {
        this.container = container;
        this.validation_instance = container.parsley();
    }

    Post.prototype = {
        save: function () {
            var data = this.data();
            data.action = 'pcs-save-post';

            if (this.validation_instance.validate()) {
                var promise = $.ajax({
                    url: window.ajaxurl,
                    type: 'post',
                    data: data
                });
            }
        },

        data: function () {
            return {
                'title': this.title().val(),
                'description': this.description().val(),
                'link': this.link().val(),
                'author': this.author().val(),
				'category': this.category().val(),
                'vote': this.vote().val(),
                '_wp_nonce': this.nonce().val()
            };
        },

        title: function () {
            return this.container.find('#post-title');
        },

        description: function () {
            return this.container.find('#post-description');
        },

        link: function () {
            return this.container.find('#post-link');
        },

        author: function () {
            return this.container.find('#post-author');
        },

		category: function () {
			return this.container.find('#post-category');
		},

        vote: function () {
            return this.container.find('#post-vote');
        },

        nonce: function () {
            return this.container.find('#post-nonce');
        },

        clear: function () {
            this.title().val('');
            this.description().val('');
            this.link().val('');
        }
    };

    window.pcsshare = window.pcsshare || {};
    window.pcsshare.Post = Post;

}(jQuery));
