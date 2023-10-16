ready(function () {
  if (window.dl) {
    pushPageView();
    initSearch();
    initButtonClick();
    initFormSubmit();
  }
});

var pushPageView = function () {
  var pageView = {
    event: 'pageView',
    page: preparePage(window.dl.page),
    user: window.dl.user,
  };
  console.log(pageView); // remove
  dataLayer.push(pageView);
};

var initButtonClick = function () {
  document.querySelectorAll('a[href],button[type=button]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      var element = {
        color: 'not_available_DL',
        target: (this.href && this.href.trim()) || 'not_available_DL',
        text: this.textContent.trim() || 'not_available_DL',
        id: createElementID(this),
      };
      var buttonClick = {
        event: 'buttonClick',
        page: preparePage(window.dl.page),
        user: window.dl.user,
        element: element,
      };
      dataLayer.push(buttonClick);
      console.log(buttonClick); // remove
    });
  });
};

var initFormSubmit = function () {
  document.querySelectorAll('form:not([data-ajax]):not(.wpcf7-form)').forEach(function (el) {
    el.addEventListener('submit', function () { handleFormSubmit(el); });
  });
  document.querySelectorAll('.wpcf7').forEach(function (el) {
    el.addEventListener('wpcf7mailsent', function () { handleFormSubmit(el); });
  });
};

var initSearch = function () {
  document.querySelectorAll('form[data-search]').forEach(function (el) {
    el.addEventListener('submit', function () { handleSearch(el); });
  });
};

var handleFormSubmit = function (el) {
  var formContainer = el.closest('[data-form]');
  var form = {
    id: (formContainer && formContainer.id) || el.id || 'not_available_DL',
    type: (formContainer && formContainer.dataset.formType) || el.dataset.formType || 'not_available_DL',
  };

  if (el.id == 'commentform') {
    form.id =  'article~' + global.post.id;
    form.type = 'comment';
  } else if (el.name == 'shp_footer-try-us-form') {
    if (el.closest('#footer')) {
      form.id =  'footer';
      form.type = 'trial';
    } else {
      form.id =  'article~' + global.post.id;
      form.type = 'trial';
    }
  } else if (el.name == 'newsletter-form') {
    form.id =  'footer';
    form.type = 'subscribe';
  }

  var user = window.dl.user;

  var nameInput = el.querySelector('[data-name] input');
  var name = nameInput && nameInput.value;
  if (name) {
    var fullNameArray = name.split(' ');
    user.name = fullNameArray.shift() || 'not_available_DL';
    user.surname = fullNameArray.join(' ') || 'not_available_DL';
  }

  var emailInput = el.querySelector('input[type=email]');
  var email = emailInput && emailInput.value.trim().toLowerCase();
  if (email) {
    user.email = email;
    user.accountHash = sha256(email);
  }
  pushFormSubmit(form, user);
};

var pushFormSubmit = function (form, user) {
  var formSubmit = {
    event: 'formSubmit',
    page: preparePage(window.dl.page),
    form: form,
    user: user,
  };
  dataLayer.push(formSubmit);
  console.log(formSubmit); // remove
};

var handleSearch = function (el) {
  var user = window.dl.user;
  var searchInput = el.querySelector('input[type=search]');
  var term = searchInput && searchInput.value;

  var search = {
    event: 'search',
    search: {
      type: 'page',
      term: term,
      results: {
        articles: -1,
        categories: -1,
        products: -1,
        other: -1,
      },
    },
    page: preparePage(window.dl.page),
    user: user,
  };
  dataLayer.push(search);
  console.log(search); // remove
};

var preparePage = function (page) {
  page.path = window.location.pathname;
  page.url = window.location.href;
  if (window.location.search || window.location.hash) {
   page.params = window.location.search + window.location.hash;
  }
  return page;
};

var createElementID = function (el) {
  var parentWithID = el.closest('[id]');
  var parentID = parentWithID ? parentWithID.id : false;
  var slug = slugify(el.textContent) || slugify(el.ariaLabel);
  
  if (parentID == 'shp_navigation') {
    parentID = 'menu';
  } else if (el.href) {
    var addonsUrls = ['https://doplnky.shoptet.cz', 'https://doplnky.shoptet.sk', 'https://alkalmazasok.shoptet.hu'];
    addonsUrls.forEach(function (url) {
      if (el.href.startsWith(url)) {
        var urlArray = el.href.split('?');
        if (urlArray[1]) {
          el.id = 'addons~' + urlArray[1].replaceAll('utm_', '').replaceAll('source=shoptet_', 'source=').replaceAll('&', '~').replaceAll('=', '~');
          console.log(el.id);
        }
      }
    });
  }
  
  var id;
  if (el.id) {
    id = el.id;
  } else if (parentID && slug) {
    id = parentID + '~' + slug;
  } else if (parentID) {
    id = parentID;
  } else if (slug) {
    id = slug;
  } else {
    id = 'not_available_DL';
  }
  return id;
};