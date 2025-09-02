(function ($) {
  $.fn.eventFilterPlugin = function (options) {
    /** SETTINGS */
    var defaultSettings = {
      selectors: {
        autocompleteContainer: ".search-filter-container",
        autocompleteInput: ".filter-autocomplete",
        autocompleteReset: ".filter-controls-reset[data-reset=autocomplete]",
        itemList: ".event-list",
        item: ".event",
        itemTitle: ".headline",
        itemContent: ".event-summary",
        itemCount: ".event-count",
        filteredItem: ".event.filtered",
        filterSuggestionList: ".filter-result",
        filterSuggestionEntry: ".filter-entry",
        noResults: ".no-results",
        activeFilterList: ".active-filter-list",
        removeFilters: ".remove-filters",
        activeFilters: ".active-filters",
        resetFilter: ".filter-controls-reset",
        activeDropdown: ".dropdown-toggle.active",
        desktopFilter: ".desktop-filters",
        mobileFilter: ".mobile-filters",
        mobileFilterToggle: ".mobile-filter-toggle",
        mobileFilterList: ".filter-categories",
        mobileFilterListEntry: ".filter-category",
        typeFilter: ".category-filter",
        dropdownContainer: ".dropdown-filters",
        datePicker: ".event-date-picker",
        datePickerButton: ".filter-button",
        datePickerMobile: ".event-date-picker.mobile",
        datePickerReset: ".filter-controls-reset[data-reset=date]",
        noCategoryAlert: ".alert-no-category",
      },
      texts: {
        noFilterSuggestionFound:
          "<h3>Für den von Ihnen eingegebenen Begriff konnten wir leider keine Ergebnisse finden.</h3>",
        updateFilter: "Filter ändern",
        addFilter: "Filter hinzufügen",
        autocompleteTitle: "Im Titel: ",
        autocompleteContent: "Im Inhalt: ",
        autocompleteTag: "In den Schlagworten: ",
      },
      defaultFilterTypes: [
        {
          id: "category",
          dropdownToggleSelector: ".category-filter .dropdown-toggle",
          filterEntrySelector: ".category-filter a.filter-entry",
          filterAllSelector: "filter-all",
          filterListItemSelector: ".category-filter .dropdown-menu a",
          displayType: "dropdown",
          label: "Alle Themen",
          multiLabel: "Mehrere Themen ausgewählt",
          isMultiselect: true,
        },
        {
          id: "type",
          dropdownToggleSelector: ".type-filter .dropdown-toggle",
          filterEntrySelector: ".type-filter a.filter-entry",
          filterAllSelector: "filter-all",
          filterListItemSelector: ".type-filter .dropdown-menu a",
          displayType: "dropdown",
          label: "Alle Formate",
          multiLabel: "Mehrere Formate ausgewählt",
          isMultiselect: true,
        },
        {
          id: "location",
          dropdownToggleSelector: ".location-filter .dropdown-toggle",
          dropdownSelector: ".location-filter .dropdown-menu",
          filterEntrySelector: ".location-filter a.filter-entry",
          filterAllSelector: "filter-all",
          filterListItemSelector: ".location-filter .dropdown-menu a",
          displayType: "dropdown",
          label: "Alle Orte",
          multiLabel: "Mehrere Orte ausgewählt",
          isMultiselect: true,
        },
        {
          id: "lecturer",
          dropdownToggleSelector: ".lecturer-filter .dropdown-toggle",
          dropdownSelector: ".lecturer-filter .dropdown-menu",
          filterEntrySelector: ".lecturer-filter a.filter-entry",
          filterAllSelector: "filter-all",
          filterListItemSelector: ".lecturer-filter .dropdown-menu a",
          displayType: "dropdown",
          label: "Alle Dozierenden",
          multiLabel: "Mehrere Dozierende ausgewählt",
          isMultiselect: true,
        },
      ],
      minInputLength: 1,
      filterData: __filterData,
      categoryTree: __categoryTree,
      hasDateFilter: true,
      dateFilterLabel: "Alle Termine",
      dateFilterLabelActive: "Zeitraum",
      noCategoryAlert:
        '<div class="alert alert-info alert-no-category"><strong>Für die von Ihnen gewählte Kategorie bieten wir aktuell keine Veranstaltungen an, daher werden Ihnen alle Ergebnisse angezeigt.</strong></div>',
      ongoingEventType: 16,
    };

    const settings = $.extend({}, defaultSettings, options);
    const selectors = settings.selectors;

    /** VARIABLES */
    let __currentFilters = [];
    const keys = {
      ESC: 27,
      TAB: 9,
      RETURN: 13,
      UP: 38,
      DOWN: 40,
    };

    /** METHODS */
    // helper to get url param
    const getUrlParameter = function getUrlParameter(sParam) {
      const sPageURL = window.location.search.substring(1);
      const sURLVariables = sPageURL.split("&");
      let sParameterName = "";

      for (var i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split("=");

        if (sParameterName[0] === sParam) {
          return sParameterName[1] === undefined
            ? true
            : sParameterName[0] + "=" + decodeURIComponent(sParameterName[1]).replace(/\+/g, " ");
        }
      }
    };

    // navigation logic for pressing down in filter suggestion list
    const handleFilterKeypressDown = function handleFilterKeypressDown(e) {
      const index = $(selectors.filterSuggestionEntry + ".active").index();

      if (
        $(selectors.filterSuggestionList + " li")[index + 1] &&
        $(selectors.filterSuggestionList + " li")
          .eq(index + 1)
          .hasClass("filter-entry")
      ) {
        $(selectors.filterSuggestionEntry + ".active").removeClass("active");
        $(selectors.filterSuggestionList + " li")
          .eq(index + 1)
          .addClass("active");
      } else if (
        $(selectors.filterSuggestionList + " li")[index + 2] &&
        $(selectors.filterSuggestionList + " li")
          .eq(index + 2)
          .hasClass("filter-entry")
      ) {
        $(selectors.filterSuggestionEntry + ".active").removeClass("active");
        $(selectors.filterSuggestionList + " li")
          .eq(index + 2)
          .addClass("active");
      }

      e.preventDefault();
      return true;
    };

    // navigation logic for pressing up in filter suggestion list
    const handleFilterKeypressUp = function handleFilterKeypressUp(e) {
      const index = $(selectors.filterSuggestionEntry + ".active").index();

      if (
        $(selectors.filterSuggestionList + " li")[index - 1] &&
        $(selectors.filterSuggestionList + " li")
          .eq(index - 1)
          .hasClass("filter-entry")
      ) {
        $(selectors.filterSuggestionEntry + ".active").removeClass("active");
        $(selectors.filterSuggestionList + " li")
          .eq(index - 1)
          .addClass("active");
      } else if (
        $(selectors.filterSuggestionList + " li")[index - 2] &&
        $(selectors.filterSuggestionList + " li")
          .eq(index - 2)
          .hasClass("filter-entry")
      ) {
        $(selectors.filterSuggestionEntry + ".active").removeClass("active");
        $(selectors.filterSuggestionList + " li")
          .eq(index - 2)
          .addClass("active");
      }

      e.preventDefault();
      return true;
    };

    // add currently selected filter in search input on enter
    const handleFilterKeypressEnter = function handleFilterKeypressEnter(e) {
      if (
        $(selectors.autocompleteInput).val().length > settings.minInputLength &&
        $(selectors.filterSuggestionEntry + ".active").length > 0
      ) {
        /* updateDefaultFilters($(selectors.filterSuggestionEntry + ".active").data("type")); */
        const filterType = $(selectors.filterSuggestionEntry + ".active").data("type");
        const filterId = $(selectors.filterSuggestionEntry + ".active").data("filter");

        clickOnFilterEntry(filterType, filterId);
      }

      e.preventDefault();
      return true;
    };

    // suggestion logic for search input filter
    const handleFilterKeypress = function handleFilterKeypress() {
      const filterValue = $(selectors.autocompleteInput).val();

      // only show suggestions if more than 2 chars are entered
      if (filterValue.length > settings.minInputLength) {
        $(selectors.filterSuggestionList).show();
        $(selectors.filterSuggestionList).html("");

        const hasContentSuggestion = appendContentFilterSuggestionsToResult(filterValue);

        let hasTypeSuggestion = false;
        settings.defaultFilterTypes.forEach(function (filterType) {
          hasSuggestion = addFilterSuggestionsByType(filterValue.toLowerCase(), filterType.id);
          if (hasSuggestion) {
            hasTypeSuggestion = true;
          }
        });

        if (!hasContentSuggestion && !hasTypeSuggestion) {
          $(selectors.filterSuggestionList).append(settings.texts.noFilterSuggestionFound);
        } else {
          highlightFilterValue(filterValue);
        }
      } else {
        $(selectors.filterSuggestionList).hide();
      }
    };

    // highlights the input value within the current filter suggestion
    const highlightFilterValue = function highlightFilterValue(filterValue) {
      $(selectors.filterSuggestionList + ".filter a").each(function () {
        const regex = new RegExp(filterValue, "gi");
        const highlightedText = $(this)
          .text()
          .replace(regex, function (str) {
            return "<span>" + str + "</span>";
          });

        $(this).html(highlightedText);
      });
    };

    // adds found type filter suggestions to the filter suggestion list
    function appendFilterSuggestionsToResult(filters, type) {
      filters.forEach(function (filter) {
        $(selectors.filterSuggestionList).append(
          '<li class="type-filter filter-entry" data-type="' +
            type +
            '" data-filter="' +
            filter.id +
            '" data-label="' +
            filter.name +
            '"><a>' +
            filter.name +
            "</a></li>"
        );
      });
    }

    // adds found filter suggestions to the filter suggestion list
    const appendContentFilterSuggestionsToResult = function appendContentFilterSuggestionsToResult(
      filterValue
    ) {
      const hasContentFilter = settings.filterData.allContents.includes(filterValue.toLowerCase());
      const hasTitleFilter = settings.filterData.allTitles.includes(filterValue.toLowerCase());

      if (hasContentFilter || hasTitleFilter) {
        $(selectors.filterSuggestionList).append("<li><h3>Inhalt</h3></li>");

        if (hasContentFilter) {
          $(selectors.filterSuggestionList).append(
            '<li class="content-filter filter-entry" data-type="content" data-label="' +
              filterValue +
              '" data-filter="' +
              filterValue +
              '"><a>Im gesamten Angebot: "<span>' +
              filterValue +
              '"</span></a></li>'
          );
        }

        if (hasTitleFilter) {
          $(selectors.filterSuggestionList).append(
            '<li class="content-filter filter-entry" data-type="title" data-label="' +
              filterValue +
              '" data-filter="' +
              filterValue +
              '"><a>Im Angebotstitel: "<span>' +
              filterValue +
              '"</span></a></li>'
          );
        }

        return true;
      }

      return false;
    };

    // searches for filter suggestions by type and appends them to the result list
    const addFilterSuggestionsByType = function addFilterSuggestionsByType(filter, type) {
      let filters = [];

      filters = settings.filterData[type].data.filter(function (element) {
        if (element && element.name) {
          return element.name.toLowerCase().includes(filter.toLowerCase());
        }

        return false;
      });

      if (filters.length > 0) {
        $(selectors.filterSuggestionList).append(
          "<li><h3>" + __filterData[type].labelPlural + "</h3></li>"
        );

        appendFilterSuggestionsToResult(filters, type);
        return true;
      }

      return false;
    };

    const initDateFilter = function initDateFilter() {
      const localeDE = {
        format: "DD.MM.YYYY",
        separator: " - ",
        applyLabel: "Übernehmen",
        cancelLabel: "Abbrechen",
        fromLabel: "Von",
        toLabel: "Bis",
        weekLabel: "W",
        daysOfWeek: ["So", "Mo", "Di", "Mi", "Do", "Fr", "Sa"],
        monthNames: [
          "Januar",
          "Februar",
          "März",
          "April",
          "Mai",
          "Juni",
          "Juli",
          "August",
          "September",
          "Oktober",
          "November",
          "Dezember",
        ],
        firstDay: 1,
      };

      $(selectors.datePicker).daterangepicker(
        {
          autoUpdateInput: false,
          minDate: moment(),
          endDate: moment().startOf("day").add(30, "day"),
          drops: "down",
          locale: localeDE,
        },
        function (start, end) {
          addDateFilter(start, end);
        }
      );

      // init mobile date range picker
      $(selectors.datePickerMobile).daterangepicker(
        {
          parentEl: selectors.mobileFilterList,
          autoUpdateInput: false,
          linkedCalendars: false,
          minDate: moment(),
          endDate: moment().startOf("day").add(30, "day"),
          drops: "down",
          locale: localeDE,
        },
        function (start, end) {
          addDateFilter(start, end, true);
        }
      );
    };

    const initFilterFromParam = function initFilterFromParam() {
      const filterParams = [];
      getUrlParameter("category") && filterParams.push(getUrlParameter("category"));
      getUrlParameter("location") && filterParams.push(getUrlParameter("location"));
      getUrlParameter("lecturer") && filterParams.push(getUrlParameter("lecturer"));
      getUrlParameter("title") && filterParams.push(getUrlParameter("title"));
      getUrlParameter("content") && filterParams.push(getUrlParameter("content"));
      getUrlParameter("format") && filterParams.push(getUrlParameter("format"));
      getUrlParameter("date") && filterParams.push(getUrlParameter("date"));

      filterParams.forEach(function (paramValue) {
        const paramValues = paramValue.split("=");
        const filterType = paramValues[0];
        const filterValue = paramValues[1];
        let filterId = null;

        if (filterType === "date") {
          const dates = filterValue.split(" - ");
          const startDate = dates[0];
          const endDate = dates[1];
          addDateFilter(startDate, endDate);
        } else if (filterType === "content" || filterType === "title") {
          addFilter(filterType, filterValue, filterValue);
        } else {
          const filterValues = filterValue.split(",");
          let multipleFilterValues = false;

          if (filterValues.length > 1) {
            multipleFilterValues = true;
          } else {
            filterId = filterValues[0];
          }

          const filtered = settings.defaultFilterTypes.find(function (item) {
            return item.id === filterType;
          });

          window.setTimeout(function () {
            $(filtered.filterEntrySelector).each(function () {
              if (multipleFilterValues) {
                if (filterValues.includes($(this).data("id").toString())) {
                  $(this).click();
                }
              } else {
                if ($(this).data("id") == filterId) {
                  $(this).click();
                }
              }
            });
          }, 0);
        }
      });
    };

    // simulate click on filter entry
    const clickOnFilterEntry = function clickOnFilterEntry(type, id) {
      const filtered = settings.defaultFilterTypes.find(function (item) {
        return item.id === type;
      });

      if (filtered) {
        $(filtered.filterEntrySelector).each(function () {
          if ($(this).data("id") == id) {
            $(this).click();
          }
        });
      } else {
        addFilter(type, id, id);
      }
    };

    // adds a new filter to dom and filter array
    const addFilter = function addFilter(type, filter, label, multiselect, multiLabel) {
      // check if filter already exists
      const filterExists = __currentFilters.findIndex(function (entry) {
        return entry.id === filter && entry.type === type;
      });

      if (filterExists === -1) {
        // remove filters of same type
        if (!multiselect) {
          __currentFilters = __currentFilters.filter(function (filter) {
            return filter.type !== type;
          });

          $('.active-filter[data-type="' + type + '"]').remove();
        }

        // add new filter to dom
        $(selectors.activeFilters).fadeIn("slow");

        if ($(selectors.activeFilters).find(`[data-id="${filter}"]`).length === 0) {
          $(selectors.activeFilters + " ul").append(
            `<li class="active-filter" data-type="${type}" data-id="${filter}" ${
              multiselect ? "data-multiselect='true'" : ""
            } style="display: none;"><strong>${
              settings.filterData[type].labelSingular
            }</strong>: ${label}</li>`
          );
        }

        $(selectors.activeFilters + " .active-filter").fadeIn("slow");

        if (type.includes("autocomplete")) {
          switch (type) {
            case "autocomplete-title":
              $(selectors.autocompleteInput).val(`${settings.texts.autocompleteTitle}${label}`);
              break;
            case "autocomplete-content":
              $(selectors.autocompleteInput).val(`${settings.texts.autocompleteContent}${label}`);
              break;
            case "autocomplete-tag":
              $(selectors.autocompleteInput).val(`${settings.texts.autocompleteTag}${label}`);
              break;
          }
          $(selectors.autocompleteInput).addClass("active");
          $(selectors.autocompleteReset).addClass("active");

          __currentFilters = __currentFilters.filter(
            (filter) => !filter.type.includes("autocomplete")
          );
        } else if (multiselect) {
          const filters = __currentFilters.find((filter) => filter.type === type);

          if (filters && filters.values.length > 0) {
            replaceDropdownLabel(type, multiLabel);
          } else {
            replaceDropdownLabel(type, label);
          }
        } else {
          replaceDropdownLabel(type, label);
        }

        // add filter to filter array
        if (multiselect) {
          addMultiselectFilterToCurrentFilters(type, filter, label);
        } else {
          __currentFilters.push({
            type,
            id: filter,
            label,
          });
        }

        // add filter to URL
        let currentUrl = new URL(window.location);

        if (multiselect) {
          if (currentUrl.searchParams.has(type)) {
            const currentValues = currentUrl.searchParams.get(type);

            if (!currentValues.includes(filter)) {
              currentUrl.searchParams.set(type, `${currentValues},${filter}`);
            }
          } else {
            currentUrl.searchParams.set(type, filter);
          }
        } else {
          currentUrl.searchParams.set(type, filter);
        }

        window.history.pushState({}, "", currentUrl);

        // reset search input
        $(selectors.filterSuggestionList).hide();
        $(selectors.autocompleteInput).val("");

        initFilter();
      }
    };

    const replaceDropdownLabel = function replaceDropdownLabel(type, label) {
      let dropdownsToReplace = $(`#${type}Dropdown`);

      if (type === "date") {
        dropdownsToReplace = $(".event-date-picker .filter-button");
      }

      dropdownsToReplace
        .addClass("active")
        .html(`<strong>${label}</strong>`)
        .each(function () {
          this.className = this.className.replace("standard", "white");
        });
    };

    const addMultiselectFilterToCurrentFilters = function addMultiselectFilterToCurrentFilters(
      type,
      filter,
      label
    ) {
      const filterIndexInCurrentFilters = __currentFilters.findIndex(
        (filter) => filter.type === type
      );

      if (filterIndexInCurrentFilters > -1) {
        const isAlreadyFiltered = __currentFilters[filterIndexInCurrentFilters].values.some(
          (val) => val.id === filter
        );

        if (!isAlreadyFiltered) {
          __currentFilters[filterIndexInCurrentFilters].values.push({ id: filter, label });
        }
      } else {
        __currentFilters.push({
          type,
          multiselect: true,
          values: [{ id: filter, label }],
        });
      }
    };

    // updates event list with the current filters
    var initFilter = function initFilter() {
      const hasActiveFilters = __currentFilters.length > 0;
      let $filteredEvents = $(selectors.item);

      $(selectors.noResults).hide();
      $(selectors.noCategoryAlert).hide();
      $filteredEvents.each(function () {
        $(this).removeClass("filtered");
      });

      if (hasActiveFilters) {
        $(selectors.activeFilters + " " + selectors.removeFilters).fadeIn("slow");
        $(selectors.mobileFilter + " " + selectors.mobileFilterToggle).text(
          settings.texts.updateFilter
        );

        // filter all events by type
        __currentFilters.forEach(function (filter) {
          $filteredEvents = filterEventsByType($filteredEvents, filter);
        });

        const hasNoResults = $filteredEvents.length <= 0;

        if (hasNoResults) {
          showActiveFilterList();
          $(selectors.noResults).show();
          $(selectors.item).hide();

          return;
        } else {
          $filteredEvents.each(function () {
            $(this).addClass("filtered");
          });
        }
      } else {
        $(selectors.activeFilters + " " + selectors.removeFilters).hide();
      }

      // get online / presence count of filtered events
      const eventCounts = { ONLINE: 0, ONSITE: 0, HYBRID: 0, ELEARNING: 0 };

      $filteredEvents.each(function () {
        const eventFormat = $(this).data("format");
        const eventType = $(this).data("type");

        if (eventCounts.hasOwnProperty(eventFormat)) {
          eventCounts[eventFormat]++;
        }

        if (eventType === 16) {
          eventCounts.ELEARNING++;
        }
      });

      $(selectors.itemList).fadeOut(function () {
        $(selectors.item).hide();
        truncateEventList();
        $(selectors.itemList).fadeIn();
      });
    };

    // filters a list of events for different types of filters
    var filterEventsByType = function filterEventsByType($events, filter) {
      if (filter.multiselect) {
        $events = filterByMultiselect($events, filter.type, filter.values);
      } else {
        switch (filter.type) {
          case "content":
            $events = filterByContent($events, filter.id);
            break;
          case "title":
            $events = filterByTitle($events, filter.id);
            break;
          case "date":
            $events = filterByDate($events, filter.id);
            break;
          default:
            $events = filterByDataAttribute($events, filter.type, filter.id);
        }
      }

      return $events;
    };

    // filters events by their title
    var filterByTitle = function filterByTitle($elements, filterValue) {
      return $elements.filter(function () {
        return $(this)
          .find(selectors.itemTitle)
          .text()
          .toLowerCase()
          .includes(filterValue.toLowerCase());
      });
    };

    // filters events by their content
    var filterByContent = function filterByContent($elements, filterValue) {
      return $elements.filter(function () {
        const content = $(this).find(selectors.itemContent).text().toLowerCase();
        const tags = $(this).data("tags").toString().toLowerCase();
        return (
          content.includes(filterValue.toLowerCase()) || tags.includes(filterValue.toLowerCase())
        );
      });
    };

    // filters events by their date
    var filterByDate = function filterByDate($elements, filterValue) {
      var filterDates = filterValue.split("-");
      var filterDateStart = moment(filterDates[0].trim(), "DD.MM.YYYY");
      var filterDateEnd = moment(filterDates[1].trim(), "DD.MM.YYYY");

      return $elements.filter(function () {
        var eventStartDates = $(this).data("date").split(",");
        var startDateIsIncluded = false;

        if ($(this).data("format") === settings.ongoingEventType) {
          // Exclude events with event type "ongoing event" from date filtering
          startDateIsIncluded = true;
        } else {
          eventStartDates.forEach(function (startDate) {
            var eventStartDate = moment(startDate, "YYYY.MM.DD");

            if (
              filterDateStart.isSameOrBefore(eventStartDate) &&
              filterDateEnd.isSameOrAfter(eventStartDate)
            ) {
              startDateIsIncluded = true;
            }
          });
        }

        return startDateIsIncluded;
      });
    };

    // filters events by multiple filter values (OR logic)
    const filterByMultiselect = function filterByMultiselect($elements, filterType, filterValues) {
      return $elements.filter(function () {
        const dataValues = $(this).data(filterType).toString().split(",");

        if (Array.isArray(filterValues)) {
          return dataValues.some((value) =>
            filterValues.some((val) => val.id === parseInt(value, 10))
          );
        } else {
          return dataValues.some((value) => filterValues === parseInt(value, 10));
        }
      });
    };

    // filters events by their data attribute
    const filterByDataAttribute = function filterByDataAttribute($elements, type, filterValue) {
      return $elements.filter(function () {
        const dataValues = $(this).data(type).toString().split(",");

        for (let index = 0; index < dataValues.length; index++) {
          if (dataValues[index] === filterValue.toString()) {
            return true;
          }
        }

        return false;
      });
    };

    // reset selected stage of mobile filter categories
    var resetSelectedMobileFilters = function resetSelectedMobileFilters(filterType) {
      if (filterType === "") {
        $(selectors.mobileFilter).find(".selected").removeClass("selected");
      }
      if (filterType === "date") {
        $(selectors.datePickerMobile).parent().removeClass("selected");
      }
      settings.defaultFilterTypes.forEach(function (defaultFilterType) {
        if (defaultFilterType.id === filterType) {
          $selectedFilter = $(
            selectors.mobileFilter + " " + selectors.mobileFilterListEntry + ".selected"
          ).children("[data-type=" + filterType + "]");
          $selectedFilter.removeClass("selected");
          $selectedFilter.parents().removeClass("selected");
        }
      });
    };

    // show the list of currently active filters if no results were found
    const showActiveFilterList = function showActiveFilterList() {
      $(selectors.activeFilterList + " .active-filter-entry").remove();

      __currentFilters.forEach(function (filter) {
        const $filteredEvents = filterEventsByType($(selectors.item), filter);
        const { type, id, label, multiselect } = filter;

        const addActiveFilterToList = function addActiveFilterToList(
          type,
          id,
          label,
          amountOfResults = null
        ) {
          $(selectors.activeFilterList).prepend(
            `<li class="active-filter-entry" data-filter-type="${type}" data-filter-id="${id}" data-filter-label="${label}">\
              Nur <a>${settings.filterData[type].labelSingular}: ${label} als Filter setzen</a> (${
              amountOfResults ?? $filteredEvents.length
            } Ergebnisse)\
            </li>`
          );
        };

        if (multiselect) {
          const { values } = filter;

          values.forEach((value) => {
            const { id, label } = value;
            const amountOfResults = filterByMultiselect($(selectors.item), type, id).length;

            addActiveFilterToList(type, id, label, amountOfResults);
          });
        } else {
          addActiveFilterToList(type, id, label);
        }
      });
    };

    // reset active stage of filter dropdowns
    const resetFilterDropdown = function resetFilterDropdown(filterType) {
      if (filterType === "") {
        $(selectors.item).removeClass("filtered");

        settings.defaultFilterTypes.forEach((defaultFilterType) => {
          if (defaultFilterType.displayType === "dropdown") {
            $(defaultFilterType.dropdownToggleSelector)
              .removeClass("active")
              .html(defaultFilterType.label)
              .each(function () {
                this.className = this.className.replace("white", "standard");
              });
            $(defaultFilterType.filterListItemSelector).removeClass("active");
          }
        });

        $(selectors.datePickerReset).removeClass("active");
        $(selectors.datePicker + " " + selectors.datePickerButton).removeClass("active");
        $(selectors.datePicker + " " + selectors.datePickerButton)
          .removeClass("active")
          .html(settings.dateFilterLabel)
          .each(function () {
            this.className = this.className.replace("white", "standard");
          });
        $(selectors.autocompleteInput).val("").removeClass("active");
        $(selectors.autocompleteReset).removeClass("active");
      } else {
        settings.defaultFilterTypes.forEach((defaultFilterType) => {
          if (defaultFilterType.displayType === "dropdown" && filterType === defaultFilterType.id) {
            $(defaultFilterType.dropdownToggleSelector)
              .removeClass("active")
              .html(defaultFilterType.label)
              .each(function () {
                this.className = this.className.replace("white", "standard");
              });
            $(defaultFilterType.filterListItemSelector).removeClass("active");
          }
        });

        if (filterType === "date") {
          $(selectors.datePickerReset).removeClass("active");
          $(selectors.datePicker + " " + selectors.datePickerButton)
            .removeClass("active")
            .html(settings.dateFilterLabel)
            .each(function () {
              this.className = this.className.replace("white", "standard");
            });
        }

        if (filterType.includes("autocomplete")) {
          $(selectors.autocompleteInput).val("").removeClass("active");
          $(selectors.autocompleteReset).removeClass("active");
        }
      }
    };

    var addDateFilter = function addDateFilter(startDate, endDate, isMobile) {
      var dateString = startDate + " - " + endDate;

      if (!(typeof startDate === "string" && typeof endDate === "string")) {
        dateString = startDate.format("DD.MM.YYYY") + " - " + endDate.format("DD.MM.YYYY");
      }
      addFilter("date", dateString, dateString);

      $(selectors.datePicker).find(selectors.datePickerButton).addClass("active").html(dateString);
      $(selectors.datePickerReset).addClass("active");

      if (isMobile === true) {
        $(selectors.datePickerMobile).parent().addClass("selected");
        $(selectors.mobileFilterList).removeClass("active");
        $(".daterangepicker").hide();
        $("body").toggleClass("unscrollable");
      }
    };

    var removeFilterQueryParam = function removeFilterQueryParam(type) {
      var currentUrl = new URL(window.location);

      if (type) {
        currentUrl.searchParams.delete(type);
      } else {
        currentUrl = currentUrl.pathname;
      }

      window.history.pushState({}, "", currentUrl);
    };

    const truncateEventList = function truncateEventList(
      $eventsToBeTruncated = $(selectors.filteredItem)
    ) {
      const hasResults = $(selectors.noResults).css("display") === "none";
      let $events = null;

      if ($eventsToBeTruncated.length === 0) {
        $events = $(selectors.item);
      } else {
        $events = $eventsToBeTruncated;
      }

      const numberOfEvents = $events.length;
      $events.hide();

      if (hasResults) $events.show();
    };

    /** EVENT HANDLERS */
    var addEventHandler = function addEventHandler() {
      // add keypress handler for search input
      $(selectors.autocompleteInput).on("keyup", function (e) {
        switch (e.which) {
          case keys.RETURN:
            handleFilterKeypressEnter(e);
            break;
          case keys.DOWN:
          case keys.TAB:
            handleFilterKeypressDown(e);
            break;
          case keys.UP:
            handleFilterKeypressUp(e);
            break;
          default:
            handleFilterKeypress(e);
        }
      });

      // hide filter suggestions on focusout
      $(selectors.autocompleteInput).on("focusout", function () {
        setTimeout(function () {
          $(selectors.filterSuggestionList).hide();
        }, 200);
      });

      // show filter suggestions on focusin
      $(selectors.autocompleteInput).on("focusin", function () {
        if ($(this).val().length > settings.minInputLength) {
          $(selectors.filterSuggestionList).show();
        }
      });

      // click handler for adding a default filter suggestion
      $(selectors.filterSuggestionList).on("click", ".type-filter", function () {
        /* updateDefaultFilters($(this).data("type")); */
        const filterType = $(this).data("type");
        const filterId = $(this).data("filter");

        clickOnFilterEntry(filterType, filterId);
      });

      // click handler for adding a content filter suggestion
      $(selectors.filterSuggestionList).on("click", ".content-filter", function () {
        addFilter($(this).data("type"), $(this).data("filter"), $(this).data("label"));
      });

      const removeMultiselectFilter = function removeMultiselectFilter(element) {
        const type = element.data("type");
        const id = element.data("id");
        const multiselect = element.data("multiselect");

        if (element.hasClass("active-filter")) {
          element.remove();
        } else {
          $(selectors.activeFilters)
            .find(`.active-filter[data-type=${type}][data-id=${id}]`)
            .remove();
        }

        removeFilterQueryParam(type);

        if (multiselect) {
          const filterIndexInCurrentFilters = __currentFilters.findIndex(
            (filter) => filter.type === type
          );

          if (__currentFilters[filterIndexInCurrentFilters].values.length > 1) {
            __currentFilters[filterIndexInCurrentFilters].values = __currentFilters[
              filterIndexInCurrentFilters
            ].values.filter((value) => value.id !== id);

            settings.defaultFilterTypes.forEach(function (filterType) {
              if (type === filterType.id) {
                $(`${filterType.filterListItemSelector}.active[data-id="${id}"`).removeClass(
                  "active"
                );
              }
            });
          } else {
            __currentFilters = __currentFilters.filter((filter) => filter.type !== type);

            settings.defaultFilterTypes.forEach(function (filterType) {
              if (type === filterType.id) {
                resetFilterDropdown(filterType.id);
              }
            });
          }
        } else {
          __currentFilters = __currentFilters.filter(
            (filter) => !(filter.id === id && filter.type === type)
          );

          settings.defaultFilterTypes.forEach(function (filterType) {
            if (type === filterType.id) {
              resetFilterDropdown(filterType.id);
            }
          });
        }

        if (type === "date") resetFilterDropdown("date");

        initFilter();
      };

      // click handler to remove active filter
      $(selectors.activeFilters).on("click", ".active-filter", function () {
        removeMultiselectFilter($(this), true);
      });

      // click handler to remove all filter entries
      $(selectors.removeFilters).on("click", function () {
        __currentFilters = [];
        $(selectors.activeFilters + " .active-filter").remove();

        resetFilterDropdown("");
        resetSelectedMobileFilters("");
        removeFilterQueryParam();

        settings.defaultFilterTypes.forEach(function (filterType) {
          if (filterType.displayType === "sidebar") {
            $(filterType.filterListItemSelector + ".active").removeClass("active");
          }
        });

        $(".filter-autocomplete").val("");
        $(this).hide();
        initFilter();
      });

      // listen to first filter prepend and add active class to appended entry
      $(selectors.filterSuggestionList).on("append", function () {
        if (
          $(selectors.filterSuggestionList + " " + selectors.filterSuggestionEntry + ".active")
            .length === 0
        ) {
          $(selectors.filterSuggestionList + " " + selectors.filterSuggestionEntry)
            .eq(0)
            .addClass("active");
        }
      });

      // click handler for adding direct sidebar/dropdown type filters
      settings.defaultFilterTypes.forEach(function (filterType) {
        $(filterType.filterEntrySelector).on("click", function () {
          if (filterType.displayType === "dropdown") {
            if ($(this).hasClass("active")) {
              $(this).removeClass("active");
              removeMultiselectFilter($(this));
            } else {
              $(this).addClass("active");
              const filterId = $(this).data("id");
              const filterLabel = $(this).text();
              const isMultiselect = $(this).data("multiselect");

              addFilter(filterType.id, filterId, filterLabel, isMultiselect, filterType.multiLabel);

              if (!$(this).data("multiselect")) {
                $(filterType.filterEntrySelector).removeClass("active");
              }
            }
          }
        });
      });

      // click handler to only show results of one active filter in no results found list
      $(selectors.itemList).on(
        "click",
        selectors.noResults + " " + selectors.activeFilterList + " .active-filter-entry a",
        function () {
          __currentFilters = [];
          $(".active-filter").remove();

          $parent = $(this).parent();

          settings.defaultFilterTypes.forEach(function (filterType) {
            if ($parent.data("filter-type") !== filterType.id) {
              if (filterType.Type === "sidebar") {
                $(filterType.filterListItemSelector + ".active").removeClass("active");
              }
              if (filterType.displayType === "dropdown") {
                resetFilterDropdown(filterType.id);
              }
            }
          });

          if ($parent.data("filter-type") !== "date") {
            resetFilterDropdown("date");
          }

          addFilter(
            $parent.data("filter-type"),
            $parent.data("filter-id"),
            $parent.data("filter-label")
          );
        }
      );
    };

    /** INIT */
    this.initialize = function () {
      // set search input initially empty
      $(selectors.autocompleteInput).val("");

      // apply filters from URL if available
      if (
        getUrlParameter("category") ||
        getUrlParameter("title") ||
        getUrlParameter("content") ||
        getUrlParameter("format") ||
        getUrlParameter("location") ||
        getUrlParameter("lecturer") ||
        getUrlParameter("date")
      ) {
        initFilterFromParam();
      }

      // init event handlers
      addEventHandler();

      if (settings.hasDateFilter) {
        initDateFilter();
      }

      truncateEventList();

      return this;
    };

    return this.initialize();
  };
})(jQuery);

// jquery append listener
(function ($) {
  var origAppend = $.fn.append;

  $.fn.append = function () {
    return origAppend.apply(this, arguments).trigger("append");
  };
})(jQuery);
