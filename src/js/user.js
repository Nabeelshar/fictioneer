// =============================================================================
// REPLACE PROFILE IMAGE IN ELEMENTS
// =============================================================================

/**
 * Append image with avatar to target element.
 *
 * @since 4.0
 */

function fcn_replaceProfileImage(target, avatar) {
  // Setup
  const old = target.querySelector('.user-icon');

  // Add avatar to view
  if (old) {
    const img = document.createElement('img');

    img.classList.add('user-profile-image');
    img.src = avatar;
    old.remove();
    target.appendChild(img);
  }
}

// =============================================================================
// GET/SET USER AVATAR URL
// =============================================================================

/**
 * Save avatar image in local storage and add it to the document.
 *
 * @since 4.0
 * @see fcn_isValidUrl(url)
 * @see fcn_replaceProfileImage(element, avatar)
 *
 * @param {String} avatar - Avatar URL.
 * @param {Boolean} [save=] - Whether to save in local storage. Default true.
 */

function fcn_setProfileImage(avatar, save = true) {
  // Check if URL is valid
  if (!avatar || !fcn_isValidUrl(avatar)) {
    return;
  }

  // Save in local storage for later
  if (save) {
    localStorage.setItem('fcnProfileAvatar', avatar);
  }

  // replace user icon with avatar
  _$$('a.subscriber-profile')?.forEach(element => {
    fcn_replaceProfileImage(element, avatar);
  });
}

/**
 * Get avatar from local storage or pull it from the server.
 *
 * @since 4.0
 * @see fcn_isValidUrl(url)
 * @see fcn_setProfileImage(avatar)
 * @see fcn_getUserAvatar()
 */

function fcn_getProfileImage() {
  // Look for cached image URL
  let avatar = localStorage.getItem('fcnProfileAvatar');

  // If image URL found but user not logged in, remove it from local storage
  if (!fcn_isLoggedIn) {
    localStorage.removeItem('fcnProfileAvatar');
    return;
  }

  // Check if URL is valid
  if (!fcn_isValidUrl(avatar)) {
    avatar = false;
  }

  if (avatar) {
    // Set profile image if URL has been found
    fcn_setProfileImage(avatar);
  } else {
    // Pull avatar from server
    fcn_getUserAvatar();
  }
}

/**
 * Fetch avatar from server via AJAX.
 *
 * @since 4.0
 * @see fcn_setProfileImage(avatar)
 */

function fcn_getUserAvatar() {
  // Request
  fcn_ajaxGet({
    'action': 'fictioneer_ajax_get_avatar',
    'fcn_fast_ajax': 1
  })
  .then(response => {
    // Check for success
    if (response.success) {
      // Set avatar
      fcn_setProfileImage(response.data.url);
    }
  })
  .catch(() => {
    // You could use a default avatar here
    if (fcn_theRoot.dataset.defaultAvatar) {
      fcn_setProfileImage(fcn_theRoot.dataset.defaultAvatar, false);
    }
  });
}

// Initialize
if (fcn_isLoggedIn || fcn_theRoot.dataset.ajaxAuth) {
  fcn_getProfileImage();
}

// =============================================================================
// FETCH RELEVANT USER DATA
// =============================================================================

var /** @type {Object} */ fcn_userData;

function fcn_initializeUserData() {
  fcn_userData = fcn_getUserData();
  fcn_fetchUserData();
}

function fcn_getUserData() {
  // Get JSON string from local storage
  const data = fcn_parseJSON(localStorage.getItem('fcnUserData'));

  // Parse and return JSON string if valid, otherwise return new JSON
  return data ??
    {
      'lastLoaded': 0,
      'timestamp': 0,
      'follows': false,
      'reminders': false,
      'checkmarks': false,
      'bookmarks': {},
      'fingerprint': false
    };
}

function fcn_setUserData(data) {
  // Update local storage
  localStorage.setItem('fcnUserData', JSON.stringify(data));
}

function fcn_fetchUserData() {
  // Only update from server after some time has passed (e.g. 60 seconds)
  if (fcn_ajaxLimitThreshold < fcn_userData['lastLoaded']) {
    const fcn_eventUserDataReady = new CustomEvent('fcnUserDataReady', {
      detail: { data: fcn_userData, time: new Date() },
      bubbles: false,
      cancelable: true
    });

    document.dispatchEvent(fcn_eventUserDataReady);
    return;
  }

  // Request
  fcn_ajaxGet({
    'action': 'fictioneer_ajax_get_user_data',
    'fcn_fast_ajax': 1
  })
  .then(response => {
    if (response.success) {
      // Assign
      fcn_userData = response.data;
      fcn_userData['lastLoaded'] = Date.now();

      // Prepare custom event
      const eventReady = new CustomEvent('fcnUserDataReady', {
        detail: { data: response.data, time: new Date() },
        bubbles: true,
        cancelable: false
      });

      // Set in local storage
      fcn_setUserData(fcn_userData);

      // Fire event
      document.dispatchEvent(eventReady);
    } else {
      // Something went wrong, possibly logged-out; clear local storage
      localStorage.removeItem('fcnUserData');
      fcn_userData = false;

      // Prepare custom event
      const eventFailure = new CustomEvent('fcnUserDataFailed', {
        detail: { response: response, time: new Date() },
        bubbles: true,
        cancelable: false
      });

      // Fire event
      document.dispatchEvent(eventFailure);
    }
  })
  .catch(error => {
    // Something went extremely wrong; clear local storage
    localStorage.removeItem('fcnUserData');
    fcn_userData = false;

    // Prepare custom event
    const eventError = new CustomEvent('fcnUserDataError', {
      detail: { error: error, time: new Date() },
      bubbles: true,
      cancelable: false
    });

    // Fire event
    document.dispatchEvent(eventError);
  });
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
  if (fcn_isLoggedIn || fcn_theRoot.dataset.ajaxAuth) {
    fcn_initializeUserData();
  }
});
