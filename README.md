# EDM TYPO3

A Typo3 extension that interacts with Education Manager (EDM).

## Table of contents

[[_TOC_]]

## Necessary Typo3 Setup

In order for the extension to work properly, there are some things to configure within your Typo3 instance.

### TypoScript

Firstly, you should create a root template that defines the following TypoScript constants (not setup!):

```typoscript
plugin.tx_edmtypo3 {
    edm {
        url = <URL of your EDM instance>

        auth {
            // OAuth App (configured in EDM) for general authentication, i.e. listing events
            anonymous {
                clientId = <Client ID for general authentication>
                clientSecret = <Client Secret for general authentication>
            }
      
            // OAuth App (configured in EDM) for actual user authentication if EDM Login Portal is activated (optional)
            profile {
                clientId = <Client ID if EDM Login Portal is activated>
                redirectUri = <Redirect URI after successful login>
            }
        }
    }

    // Used in Fluid templates to load correct file versions
    versions {
        form = <Version number of the registration form, i.e 2.0.3>
        config = <Version number of the registration form config, i.e 1.3.1>
        profile-snippet = <Version number of the EDM profile snippet, i.e 1.0.0>
    }

    errors {
        enrollment {
            mailFrom = <Sender of the error mail>
            mailTo = <Recipient of the error mail>
            mailSubject = <Subject of the error mail>
        }
    }

    // Page UIDs that are used to properly link from TYPO3 templates
    pageuids {
        eventSearch = <puid>
        eventDetail = <puid>
        eventEnrollment = <puid>
        locationDetail = <puid>
        lecturerDetail = <puid>
    }

    customConditions {
        // Used in EventController to specify EDM event types for which all events should be shown, regardless of dates or price
        eventTypes {
            showAllEvents = <eventId>
        }
    }
}
```

### Page Structure

How you use the extension is totally up to you, but in general the following page setup is advisable:

- A page containing the `Eventdetail` plugin ("Veranstaltungen: Detailseite"). This shows detailed information about a single event.
- A page containing the `Enrollmentnew` plugin ("Veranstaltungen: Anmeldung"). The template must integrate our `edm-registration-form` React application to handle enrollments (see below).
- A page containing either the `Eventsearch` plugin ("Veranstaltungen: Suche") or using the `Eventlist` plugin ("Veranstaltungen: Liste") wherever you want to. Both list events, but the `Eventsearch` plugin provides `filterData` and `categoryTree` to the Fluid template, making it possible to build search/filter functionality on top. Whereas the `Eventlist` plugin offers more filters in the TYPO3 backend.

### Enrollment to Events

To enable enrollments in EDM through the website, the following has to be ensured:

- A page includes the `Enrollmentnew` plugin.
- The `Private/Ext/edm/Templates/Enrollment/New.html` template includes the `edm-registration-form` Javascript files (`form` and `config`)
- The `Private/Ext/edm/Templates/Enrollment/New.html` template exposes the `accessToken` provided by the controller as a Javascript variable called `__authToken`
- The `edm-registration-form` is initialised like so:

```javascript
EdmForm.init(
    EdmForm.config, // DO NOT CHANGE
    __event, // The Javascript variable the `event` from the controller is stored into
    "event", // DO NOT CHANGE
    document.getElementById("edm-registration-form"), // CSS selector of an HTML element the form is attached to
    680, // Max width of the form application; defaults to "none"
    16, // Padding that should be applied to the form application; defaults to 0
    "", // Only relevant for "newsletter" variant, not for "event"
    "", // Only relevant for "newsletter" variant, not for "event"
    "de" // Language the form should render in, currently works with "de", "en", "fr" and "es"; defaults to "de"
);
```

## Extend Extension

It is possible to further extend the extension for your needs. This can be done either through hooks or by adding to already existing controller actions.

Since the provided templates are very rudimentary, it is advised to build your own in a separate extension, overwriting the ones from the `edm-typo3` extension.
