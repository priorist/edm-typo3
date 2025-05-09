# EDM TYPO3

A Typo3 extension that interacts with Education Manager (EDM).

## Table of contents

[[_TOC_]]

## Necessary Typo3 Setup

In order for the extension to work properly, there are some things to configure within your Typo3 instance.

### TypoScript

Firstly, you should create a root template that defines the following TypoScript constants:

```typoscript
plugin.tx_edmtypo3 {
    edm {
        url = <URL of your EDM instance>

        auth {
            anonymous {
                clientId = <Client ID for general authentication>
                clientSecret = <Client Secret for general authentication>
            }
      
            profile {
                clientId = <Client ID if EDM Login Portal is activated>
                redirectUri = <Redirect URI after successful login>
            }
        }
    }

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
        home = 1
        eventSearch = 2
        eventDetail = 3
        eventEnrollment = 4
        locationDetail = 6
        lecturerDetail = 8
        footer = 66
        privacy = 70
        newsletter = 119
        404 = 35
    }

    customConditions {
        // Used in EventController to specify EDM event types for which all events should be shown, regardless of dates or price
        eventTypes {
            showAllEvents = 5
        }
    }
}
```

## Extend Extension

It is possible to further extend the extension for your needs. This can be done either through hooks or by adding to already existing controller actions.
