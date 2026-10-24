/**
 * ====================================================================
 * StudentHub Portal - Campus Locations Module
 * Dependent Dropdowns (Country -> State -> City Cascading Selector)
 * ====================================================================
 * Key Focus:
 * 1. Populates Country dropdown from location dataset
 * 2. Dynamically populates State dropdown upon Country change
 * 3. Dynamically populates City dropdown upon State change
 * 4. Gracefully disables and clears dependent children when parent resets
 * 5. Provides bidirectional sync with Student directory filter
 * ====================================================================
 */

(function (global) {
    'use strict';

    // Default built-in location data structure
    const defaultLocationData = {
        "India": {
            "Gujarat": ["Ahmedabad", "Surat", "Vadodara", "Rajkot", "Gandhinagar", "Bhavnagar"],
            "Maharashtra": ["Mumbai", "Pune", "Nagpur", "Nashik", "Aurangabad"],
            "Karnataka": ["Bengaluru", "Mysuru", "Mangaluru", "Hubballi"],
            "Delhi": ["New Delhi", "North Delhi", "South Delhi", "Dwarka"],
            "Tamil Nadu": ["Chennai", "Coimbatore", "Madurai", "Tiruchirappalli"]
        },
        "USA": {
            "California": ["San Jose", "San Francisco", "Los Angeles", "San Diego"],
            "Texas": ["Austin", "Dallas", "Houston", "San Antonio"],
            "New York": ["New York City", "Buffalo", "Rochester", "Albany"]
        },
        "Germany": {
            "Bavaria": ["Munich", "Nuremberg", "Augsburg", "Regensburg"],
            "Berlin": ["Berlin Mitte", "Spandau", "Pankow", "Charlottenburg"],
            "Hesse": ["Frankfurt", "Wiesbaden", "Kassel", "Darmstadt"]
        },
        "Canada": {
            "Ontario": ["Toronto", "Ottawa", "Mississauga", "Hamilton"],
            "British Columbia": ["Vancouver", "Victoria", "Surrey", "Burnaby"],
            "Quebec": ["Montreal", "Quebec City", "Laval", "Gatineau"]
        }
    };

    let activeLocationData = defaultLocationData;

    /**
     * Updates active location dataset from fetched JSON
     * @param {object} data
     */
    function setLocationData(data) {
        if (data && typeof data === 'object') {
            activeLocationData = data;
        }
    }

    /**
     * Get active locations
     */
    function getLocationData() {
        return activeLocationData;
    }

    /**
     * Initializes cascading dependent dropdowns
     * @param {object} config
     * @param {HTMLSelectElement|string} config.countrySelect
     * @param {HTMLSelectElement|string} config.stateSelect
     * @param {HTMLSelectElement|string} config.citySelect
     * @param {Function} [config.onSelectionChange]
     * @param {string} [config.defaultAllLabel="All"]
     * @returns {object} Controller instance with reset and getValue methods
     */
    function initDependentDropdowns({
        countrySelect,
        stateSelect,
        citySelect,
        onSelectionChange = null,
        defaultAllLabel = "All"
    }) {
        const countryEl = typeof countrySelect === 'string' ? document.getElementById(countrySelect) : countrySelect;
        const stateEl = typeof stateSelect === 'string' ? document.getElementById(stateSelect) : stateSelect;
        const cityEl = typeof citySelect === 'string' ? document.getElementById(citySelect) : citySelect;

        if (!countryEl || !stateEl || !cityEl) {
            console.warn('[Locations] Missing one or more select elements for dependent dropdowns');
            return null;
        }

        /**
         * Helper to clear and append default option to select
         */
        function resetSelect(selectEl, placeholder, disabled = false) {
            selectEl.innerHTML = '';
            const defaultOpt = document.createElement('option');
            defaultOpt.value = 'all';
            defaultOpt.textContent = `-- ${placeholder} --`;
            selectEl.appendChild(defaultOpt);
            selectEl.disabled = disabled;
        }

        /**
         * Populate Countries
         */
        function populateCountries() {
            resetSelect(countryEl, `${defaultAllLabel} Countries`, false);
            const countries = Object.keys(activeLocationData).sort();
            countries.forEach(country => {
                const opt = document.createElement('option');
                opt.value = country;
                opt.textContent = country;
                countryEl.appendChild(opt);
            });
        }

        /**
         * Populate States for selected country
         */
        function populateStates(countryName) {
            if (!countryName || countryName === 'all' || !activeLocationData[countryName]) {
                resetSelect(stateEl, `${defaultAllLabel} States`, true);
                resetSelect(cityEl, `${defaultAllLabel} Cities`, true);
                return;
            }

            resetSelect(stateEl, `${defaultAllLabel} States`, false);
            resetSelect(cityEl, `${defaultAllLabel} Cities`, true);

            const states = Object.keys(activeLocationData[countryName]).sort();
            states.forEach(state => {
                const opt = document.createElement('option');
                opt.value = state;
                opt.textContent = state;
                stateEl.appendChild(opt);
            });
        }

        /**
         * Populate Cities for selected country and state
         */
        function populateCities(countryName, stateName) {
            if (!countryName || countryName === 'all' || !stateName || stateName === 'all' ||
                !activeLocationData[countryName] || !activeLocationData[countryName][stateName]) {
                resetSelect(cityEl, `${defaultAllLabel} Cities`, true);
                return;
            }

            resetSelect(cityEl, `${defaultAllLabel} Cities`, false);

            const cities = activeLocationData[countryName][stateName].slice().sort();
            cities.forEach(city => {
                const opt = document.createElement('option');
                opt.value = city;
                opt.textContent = city;
                cityEl.appendChild(opt);
            });
        }

        function triggerCallback() {
            if (typeof onSelectionChange === 'function') {
                onSelectionChange({
                    country: countryEl.value,
                    state: stateEl.value,
                    city: cityEl.value
                });
            }
        }

        // Attach event listeners
        countryEl.addEventListener('change', function () {
            populateStates(this.value);
            triggerCallback();
        });

        stateEl.addEventListener('change', function () {
            populateCities(countryEl.value, this.value);
            triggerCallback();
        });

        cityEl.addEventListener('change', function () {
            triggerCallback();
        });

        // Initial setup
        populateCountries();
        resetSelect(stateEl, `${defaultAllLabel} States`, true);
        resetSelect(cityEl, `${defaultAllLabel} Cities`, true);

        return {
            reset: function () {
                countryEl.value = 'all';
                populateStates('all');
                triggerCallback();
            },
            getSelection: function () {
                return {
                    country: countryEl.value,
                    state: stateEl.value,
                    city: cityEl.value
                };
            },
            refresh: function () {
                populateCountries();
            }
        };
    }

    // Expose Locations module
    const LocationsModule = {
        setLocationData,
        getLocationData,
        initDependentDropdowns,
        defaultLocationData
    };

    if (typeof module !== 'undefined' && module.exports) {
        module.exports = LocationsModule;
    } else {
        global.StudentHubLocations = LocationsModule;
    }

})(typeof window !== 'undefined' ? window : this);
