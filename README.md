# EDM TYPO3

A Typo3 extension that interacts with Education Manager (EDM).

## Table of contents

[[_TOC_]]
## Necessary Typo3 Setup

In order for the extension to work properly, there are some things to configure within your Typo3 instance.

### TypoScript

Firstly, you should create a root template that defines the following TypoScript constants:

```typoscript
plugin.tx_edm {
    edm {
        url = <URL of your EDM instance>

        auth {
            anonymous {
                clientId = <Client ID of anonymous OAuth app (used to display data)>
                clientSecret = <Client Secret of anonymous OAuth app (used to display data)>
            }

            password {
                clientId = <Client ID of logged-in OAuth app (used to provide a logged-in area for participants)>
                clientSecret = <Client Secret of logged-in OAuth app (used to provide a logged-in area for participants)>
            }
        }

        slugs {
            eventDescription = <Slug of your description type you want to display as the main descriptor of an event>
        }
    }

    // Pageuids can be used to link to specific pages
    pageuids {
        eventSearch = <pageUid of your event search page>
        eventDetail = <pageUid of your event detail page>
        eventEnrollment = <pageUid of your event enrollment page>
        404 = <pageUid of your generic 404 page. Used if no event is found on a detail page>
    }
}
```

### Create User & User Group

- In order for the login feature to work properly, an user and user group have to be created.
- **User Group:** create an user group called `Seminar-Teilnehmer`
- **User:** create an user called `api-teilnehmer` and assign it to the user group `Seminar-Teilnehmer`

## Extend Extension

It is possible to further extend the extension for your needs. This can be done either through hooks or by adding to already existing controller actions.
