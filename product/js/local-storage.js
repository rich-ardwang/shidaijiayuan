/*
 * File name:   local-storage.js
 * Desc:        Encapsulate local storage such as cookie, local storage and session storage.
 * Author:      Richard Wang <wanglei_gmgc@hotmail.com>
 * Create:      2020-10-12
 */

var _local = {
    // Check explorer if it support local storage or not.
    supportLocal() {
        if (!window.localStorage) {
            return false;
        }
        return true;
    },
    // Get the element count of local storage.
    keysCount() {
        return localStorage.length;
    },
    // Get all the keys of local storage.
    keys() {
        var keys = [];
        for (let i = 0; i < localStorage.length; i++) {
            keys.push(localStorage.key(i));
        }
        return keys;
    },
    // Set the K-V and also the expire time.
    set(key, value, expires) {
        let params = { key, value, expires };
        if (expires) {
            // Append the write time to the params when store them to local storage.
            // The expires support unit millisecond.
            var data = Object.assign(params, { startTime: new Date().getTime() });
            localStorage.setItem(key, JSON.stringify(data));
        } else {
            if (Object.prototype.toString.call(value) === '[object Object]') {
                value = JSON.stringify(value);
            }
            if (Object.prototype.toString.call(value) === '[object Array]') {
                value = JSON.stringify(value);
            }
            localStorage.setItem(key, value);
        }
    },
    // Get value by the key.
    get(key) {
        let item = localStorage.getItem(key);
        // Get the original JSON params and parse them.
        try {
            item = JSON.parse(item);
        } catch (error) {
            // eslint-disable-next-line no-self-assign
        }
        // If there is a value for startTime, the expire time is set.
        if (item && item.startTime) {
            let date = new Date().getTime();
            // If the left is great than the right, the expire time is up.
            if (date - item.startTime > item.expires) {
                localStorage.removeItem(name);
                return false;
            } else {
                return item.value;
            }
        } else {
            return item;
        }
    },
    // Delete element by the key.
    remove(key) {
        localStorage.removeItem(key);
    },
    // Clear all the elements.
    clear() {
        localStorage.clear();
    }
};

/**
 * sessionStorage
 */
var _session = {
    // Check explorer if it support session storage or not.
    supportSession() {
        if (!window.sessionStorage) {
            return false;
        }
        return true;
    },
    // Get value by the key.
    get: function (key) {
        var data = sessionStorage[key];
        if (!data || data === "null") {
            return null;
        }
        return JSON.parse(data).value;
    },
    // Set the K-V.
    set: function (key, value) {
        var data = {
            value: value
        };
        sessionStorage[key] = JSON.stringify(data);
    },
    // Delete element by the key.
    remove(key) {
        sessionStorage.removeItem(key);
    },
    // Clear all elements.
    clear() {
        sessionStorage.clear();
    }
};
