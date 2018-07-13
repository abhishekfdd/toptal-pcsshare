(function () {
	function Slider(containerSelector) {
		this.container = document.querySelector(containerSelector);

		this.sliderNavigation = new SliderNavigation(this.container.querySelectorAll('.slider-navigation-item'));
		this.slidesList = new SlidesList(this.container.querySelectorAll('.slider-slide'));

		this.currentSlide = 0;
		this.totalSlides = this.slidesList.total();
	}

	Slider.prototype = {
		prev: function () {
			this.activate(this.getPrevIndex());
		},

		getPrevIndex: function () {
			var slideIndex = this.currentSlide - 1;

			if (slideIndex < 0) {
				slideIndex += this.totalSlides;
			}

			return slideIndex;
		},

		next: function () {
			this.activate(this.getNextIndex());
		},

		getNextIndex: function () {
			return (this.currentSlide + 1) % this.totalSlides;
		},

		activate: function (slideIndex) {
			var slideToDeactivate = this.currentSlide;

			this.deactivateSlide(slideToDeactivate);
			this.sliderNavigation.deactivateItem(slideToDeactivate);

			this.activateSlide(slideIndex);
			this.sliderNavigation.activateItem(slideIndex);
		},

		deactivateSlide: function (slideIndex) {
			if (slideIndex < 0 || slideIndex >= this.totalSlides) {
				console.log('There is not slide #' + slideIndex);
				return;
			}

			var slide = this.slidesList.get(slideIndex);
			slide.hide();
		},

		activateSlide: function (slideIndex) {

			if (slideIndex < 0 || slideIndex >= this.totalSlides) {
				console.log('Can not move to slide #' + slideIndex);
				return;
			}

			var currentSlide = this.slidesList.get(this.currentSlide);
			var slide = this.slidesList.get(slideIndex);

			this.currentSlide = slideIndex;
			this.setLink(slide.link());
			this.setTitle(slide.title());
			this.setDescription(slide.description());
			this.setDomain(slide.domain());

			currentSlide.hide();			
			slide.show();
		},
	
		getLink: function () {
			var linkElement = this._getLinkElement();
			return linkElement.href;
		},

		setLink: function (link) {
			var linkElement = this._getLinkElement();
			linkElement.href = link;
		},

		_getLinkElement: function () {
			return this.container.querySelector('.slider-active-slide-link');
		},

		getTitle: function () {
			var titleElement = this._getTitleElement();
			return titleElement.innerText;
		},

		setTitle: function (title) {
			var titleElement = this._getTitleElement();
			titleElement.innerText = title;
		},

		_getTitleElement: function () {
			return this.container.querySelector('.slider-active-slide-title');
		},

		setDescription: function (description) {
			var descriptionElement = this._getDescriptionElement();
			return descriptionElement.innerText = description;
		},

		_getDescriptionElement: function () {
			return this.container.querySelector('.slider-active-slide-description');
		},

		setDomain: function (domain) {
			var domainElement = this._getDomainElement();
			return domainElement.innerText = domain.replace(/^www\./, '');
		},

		_getDomainElement: function () {
			return this.container.querySelector('.slider-active-slide-domain');
		},
	};

	function SlidesList(slides) {
		this.slides = slides;
	}

	SlidesList.prototype = {
		get: function (index) {
			return new Slide(this.slides[index]);
		},

		total: function () {
			return this.slides.length;
		}
	};

	function Slide(container) {
		this.container = container;
	}

	Slide.prototype = {
		title: function () {
			return this.container.alt;
		},

		img: function () {
			return this.container;
		},

		hide: function () {
			this.container.parentNode.classList.add('hidden');
		},

		show: function () {
			this.container.parentNode.classList.remove('hidden');
		},

		link: function () {
			return this.container.parentNode.href;
		},

		description: function () {
			return this.container.dataset.description;
		},

		domain: function () {
			var linkParser = document.createElement('a');
			linkParser.href = this.link();
			return linkParser.hostname;
		}
	};

	function SliderNavigation(items) {
		this.navigationItems = items;
	}

	SliderNavigation.prototype = {
		activateItem: function (index) {
			this.navigationItems[index].classList.add('slider-navigation-item-active');
		},

		deactivateItem: function (index) {
			this.navigationItems[index].classList.remove('slider-navigation-item-active');
		}
	};

	function SliderInterval(slider, delay) {
		this.slider = slider;
		this.delay = delay;
		this.intervalId = null;
	}

	SliderInterval.prototype = {
		reset: function () {
			this.stop();
			this.start();
		},

		stop: function () {
			clearInterval(this._intervalId);
			return this;
		},

		start: function () {
			var slider = this.slider;
			this._intervalId = setInterval(function () {
				slider.next();
			}, this.delay);
			return this;
		}
	};

	window.pcsshare = window.pcsshare || {};
	window.pcsshare.Slider = Slider;
	window.pcsshare.SliderInterval = SliderInterval;
}());
