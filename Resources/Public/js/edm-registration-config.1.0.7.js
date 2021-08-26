const config = {
  settings: {
    mode: 'wizard',
    enableDebug: false,
    showProgressBar: true,
    priceTypeSelect: true,
    AIS: {
      baseURL: 'https://ais.akademiesued.org',
      apiURL: 'https://ais.akademiesued.org/api/v1',
      prices: [
        {
          default: false,
          reduced: true,
          type: 'Ermäßigt',
          label: 'für Mitglieder im Paritätischen',
        },
        {
          default: true,
          reduced: false,
          type: 'Normal',
          label: 'keine Mitgliedschaft',
        },
      ],
    },
    newsletter: {
      baseURL: '/?type=20',
    },
    behaviour: {
      fadeInTimeout: 500,
      generateIcsFile: true,
      showFileSize: true,
      scrollToNextStep: true,
    },
    error: {
      messageUrl: '/ais-anmeldung-fehler',
    },
    defaults: {
      invoiceType: 'business',
      invoiceUseDispatch: false,
      invoiceDispatchEqualsInvoiceAddress: true,
    },
  },
  errorMessages: {
    required: 'Bitte füllen Sie dieses Pflichtfeld aus.',
    email: 'Bitte geben Sie eine gültige E-Mail-Adresse ein.',
    letters: 'Bitte verwenden Sie nur Buchstaben.',
    numbers: 'Bitte verwenden Sie nur Zahlen.',
    fileUpload: 'Bitte laden Sie mindestes eine Datei hoch.',
  },
  texts: {
    fileUpload: {
      introRegistrationForm:
        'Für diese Bewerbung ist ein Antrag auf Zulassung nötig. Die Textfelder im Antrag auf Zulassung sind beschreibbar. Den Antrag können Sie hier herunterladen:',
      description: 'Datei in dieses Feld ziehen oder hier klicken',
      files: 'Bisher hochgeladene Dateien:',
      error:
        'Bei der Übertragung der Datei ist ein Fehler aufgetreten. Bitte versuchen Sie es später erneut.',
      uploading: 'Datei wird hochgeladen...',
      maxFiles: 'Bitte nicht mehr als fünf Dateien hochladen.',
      maxSize: 'Bitte keine Datei hochladen, die größer als 20MB ist.',
    },
    assistance:
      'Falls Sie Unterstützungsbedarf aufgrund einer Beeinträchtigung/Behinderung haben, nehmen Sie bitte Kontakt zu uns auf unter Tel.: <a href="tel:+49 711 25298-925">+49 711 25298-925</a> oder <a href="mailto:paritaet@akademiesued.org">paritaet@akademiesued.org</a>',
    newsletter: {
      description:
        'Abonnieren Sie den Newsletter der Paritätischen Akademie Berlin und Paritätischen Akademie Süd. Darin erhalten Sie regelmäßig Informationen über unsere Veranstaltungen und Weiterbildungen und erfahren direkt, wenn neue Themen in das Programm aufgenommen werden.',
    },
    enrollment:
      'Hiermit melden Sie sich verbindlich an. Nach dem Absenden bekommen Sie von uns eine Eingangsbestätigung Ihrer Anmeldung.',
    interest:
      'Hiermit fordern Sie Informationsmaterial für den gewählten Studiengang an. Nach dem Absenden senden wir Ihnen das Informationsmaterial in Kürze zu.',
    application:
      'Hiermit bewerben Sie sich verbindlich für die ausgewählte Veranstaltung. Nach dem Absenden bekommen Sie von uns eine Eingangsbestätigung Ihrer Bewerbung.',
    addToCalendar: 'Veranstaltung zum Kalender hinzufügen',
    formError: {
      headline: 'Anmeldung nicht möglich',
      text:
        'Leider ist die Anmeldung zur oben genannten Veranstaltung nicht möglich. Die Veranstaltung hat entweder bereits begonnen oder stattgefunden oder sie ist aus anderen Gründen nicht zur Anmeldung freigegeben (z.B. keine freien Plätze).<br /><br />Werfen Sie gerne einen Blick auf unser <strong><a href="/alle-bildungsangebote/alle-angebote">gesamtes Angebot</a></strong>!<br /><br />Wenn Sie Fragen haben, können Sie sich gerne bei uns telefonisch (<a href="tel:+49 711 25298-925">+49 711 25298-925</a>) oder per E-Mail melden (<a href="mailto:paritaet@akademiesued.org">paritaet@akademiesued.org</a>)',
    },
    noMember:
      'Eine Reduktion der Kursgebühr ist leider nicht möglich, wenn Sie sich privat anmelden.',
  },
  formTypes: {
    default: {
      name: 'default',
      submitType: 'enrollment',
      mode: 'wizard',
      steps: [
        {
          name: 'StepAttendee',
          position: 0,
          headline: '1) Teilnehmer*in',
          buttonLabel: 'Rechnungsanschrift angeben',
          fields: ['salutation', 'firstName', 'lastName', 'email'],
        },
        {
          name: 'StepInvoice',
          position: 1,
          headline: '2) Rechnungsanschrift',
          buttonLabel: 'Zum nächsten Schritt',

          fields: ['invoiceType', 'organizationName', 'address', 'zip', 'city', 'invoiceEmail'],
        },
        {
          name: 'StepTerms',
          position: 2,
          headline: '3) Verbindlich anmelden',
          buttonLabel: 'Verbindlich anmelden',
          fields: ['priceType', 'isMember', 'newsletter', 'termsAndConditions', 'dataPrivacy'],
        },
      ],
      formSuccess: {
        headline: 'Vielen Dank für Ihre Anmeldung',
        text:
          'Wir haben Ihre Anfrage erhalten und werden Sie umgehend bearbeiten. Eine Bestätigung Ihrer Anmeldung wird Ihnen in Kürze per E-Mail zugesandt.',
      },
      formError: {
        headline: 'Hoppla, da ist etwas schief gelaufen',
        text:
          'Wir entschuldigen uns für die Unannehmlichkeiten und bitten Sie, es später erneut zu versuchen. Wenn das Problem dann nicht behoben sein sollte, kontaktieren Sie uns gerne unter <a href="mailto:info@akademiesued.org">info@akademiesued.org</a>',
      },
      buttonLabel: 'Verbindlich anmelden',
    },
    withDocuments: {
      name: 'withDocuments',
      submitType: 'application',
      mode: 'wizard',
      steps: [
        {
          name: 'StepAttendee',
          position: 0,
          headline: '1) Bewerber*in',
          buttonLabel: 'Rechnungsanschrift angeben',
          fields: ['salutation', 'firstName', 'lastName', 'email'],
        },
        {
          name: 'StepInvoice',
          position: 1,
          headline: '2) Rechnungsanschrift',
          buttonLabel: 'Zum nächsten Schritt',
          fields: ['invoiceType', 'organizationName', 'address', 'zip', 'city', 'invoiceEmail'],
        },
        {
          name: 'StepDocuments',
          position: 2,
          headline: '3) Bewerbungsunterlagen',
          buttonLabel: 'Zum nächsten Schritt',
          fields: ['fileUploadCvAndReports', 'fileUploadApplicationAndOther'],
        },
        {
          name: 'StepTerms',
          position: 3,
          headline: '4) Verbindlich bewerben',
          buttonLabel: 'Bewerbung absenden',
          fields: ['priceType', 'isMember', 'newsletter', 'termsAndConditions', 'dataPrivacy'],
        },
      ],
      formSuccess: {
        headline: 'Vielen Dank für Ihre Bewerbung',
        text:
          'Wir haben Ihre Anfrage erhalten und werden Sie umgehend bearbeiten. Eine Bestätigung Ihrer Bewerbung wird Ihnen in Kürze per E-Mail zugesandt.',
      },
      formError: {
        headline: 'Hoppla, da ist etwas schief gelaufen',
        text:
          'Wir entschuldigen uns für die Unannehmlichkeiten und bitten Sie, es später erneut zu versuchen. Wenn das Problem dann nicht behoben sein sollte, kontaktieren Sie uns gerne unter <a href="mailto:info@akademiesued.org">info@akademiesued.org</a>',
      },
      buttonLabel: 'Bewerbung absenden',
    },
    newsletter: {
      name: 'newsletter',
      submitType: 'newsletter',
      steps: [
        {
          headline: 'Newsletter-Registrierung',
          buttonLabel: 'Zum Newsletter anmelden',
          fields: [
            'newsletterSalutation',
            'firstName',
            'lastName',
            'newsletterEmail',
            'newsletterDataPrivacy',
          ],
        },
      ],
      formSuccess: {
        headline: 'Vielen Dank für Ihre Anmeldung',
        textUpdate:
          "Sie erhalten künftig in regelmäßigen Abständen unseren Newsletter.",
        textAdd:
          "Sie erhalten in Kürze eine Email zur Bestätigung Ihrer Newsletter-Anmeldung. Bitte prüfen Sie den Posteingang (evtl. auch den Spam-Ordner) und bestätigen Sie Ihre Anmeldung.",
      },
      formError: {
        headline: 'Hoppla, da ist etwas schief gelaufen',
        text:
          'Leider ist bei der Bearbeitung Ihrer Anfrage ein technisches Problem aufgetreten. Bitte versuchen Sie es in Kürze erneut. Wenn das Problem weiterhin besteht, lassen Sie es uns bitte wissen.',
      },
    },
    SRH: {
      name: 'SRH',
      submitType: 'interest',
      mode: 'wizard',
      steps: [
        {
          name: 'StepAttendee',
          position: 0,
          headline: '1) Interessent*in',
          buttonLabel: 'Weiter',
          fields: [
            'salutation',
            'firstName',
            'lastName',
            'address',
            'zip',
            'city',
            'prospectEmail',
            'phone',
          ],
        },
        {
          name: 'StepTerms',
          position: 1,
          headline: '2) Datenschutz',
          buttonLabel: 'Anfrage absenden',
          fields: ['srhConsent', 'newsletter', 'termsAndConditions', 'dataPrivacy'],
        },
      ],
      formSuccess: {
        headline: 'Vielen Dank für Ihr Interesse',
        text:
          'Wir haben Ihre Anfrage erhalten und werden Sie umgehend bearbeiten. Das Informationsmaterial zum Studiengang wird Ihnen in Kürze zur Verfügung gestellt.',
      },
      formError: {
        headline: 'Hoppla, da ist etwas schief gelaufen',
        text:
          'Wir entschuldigen uns für die Unannehmlichkeiten und bitten Sie, es später erneut zu versuchen. Wenn das Problem dann nicht behoben sein sollte, kontaktieren Sie uns gerne unter <a href="mailto:info@akademiesued.org">info@akademiesued.org</a>',
      },
      buttonLabel: 'Anfrage absenden',
    },
    info: {
      name: 'info',
      submitType: 'enrollment',
      mode: 'wizard',
      steps: [
        {
          name: 'StepAttendee',
          position: 0,
          headline: '1) Teilnehmer*in',
          buttonLabel: 'Weiter',
          fields: ['salutation', 'firstName', 'lastName', 'email'],
        },
        {
          name: 'StepTerms',
          position: 1,
          headline: '2) Verbindlich anmelden',
          buttonLabel: 'Verbindlich anmelden',
          fields: ['newsletter', 'termsAndConditions', 'dataPrivacy'],
        },
      ],
      formSuccess: {
        headline: 'Vielen Dank für Ihre Anmeldung',
        text:
          'Wir haben Ihre Anfrage erhalten und werden Sie umgehend bearbeiten. Eine Bestätigung Ihrer Anmeldung wird Ihnen in Kürze per E-Mail zugesandt.',
      },
      formError: {
        headline: 'Hoppla, da ist etwas schief gelaufen',
        text:
          'Wir entschuldigen uns für die Unannehmlichkeiten und bitten Sie, es später erneut zu versuchen. Wenn das Problem dann nicht behoben sein sollte, kontaktieren Sie uns gerne unter <a href="mailto:info@akademiesued.org">info@akademiesued.org</a>',
      },
      buttonLabel: 'Verbindlich anmelden',
    },
  },
  fields: {
    salutation: {
      type: 'radio',
      typeConfig: {
        radioGroupProps: {
          row: true,
        },
      },
      name: 'salutation',
      label: 'Anrede',
      size: 12,
      show: true,
      required: true,
      variant: 'outlined',
      options: [
        {
          value: 'F',
          label: 'Frau',
        },
        {
          value: 'M',
          label: 'Herr',
        },
        {
          value: 'NON_BINARY',
          label: 'Divers',
        },
      ],
    },
    firstName: {
      type: 'text',
      name: 'firstName',
      validation: 'lettersOnly',
      size: 6,
      label: 'Vorname',
      show: true,
      required: true,
      variant: 'outlined',
      autocomplete: 'given-name',
    },
    lastName: {
      type: 'text',
      name: 'lastName',
      validation: 'lettersOnly',
      size: 6,
      label: 'Nachname',
      show: true,
      required: true,
      variant: 'outlined',
      autocomplete: 'family-name',
    },
    phone: {
      type: 'text',
      name: 'phone',
      validation: 'numbersOnly',
      size: 6,
      label: 'Telefon-Nr.',
      show: true,
      required: true,
      variant: 'outlined',
      autocomplete: 'tel',
    },
    email: {
      type: 'email',
      name: 'email',
      validation: 'email',
      size: 12,
      label: 'E-Mail-Adresse',
      show: true,
      required: true,
      variant: 'outlined',
      helperText: 'Diese Adresse wird für die Seminarkommunikation genutzt',
      autocomplete: 'email',
    },
    impairment: {
      type: 'checkbox',
      typeConfig: {
        formGroupProps: {
          row: true,
        },
      },
      name: 'impairment',
      size: 12,
      label: 'Ich habe eine Beeinträchtigung/Behinderung',
      show: true,
      required: false,
      variant: 'outlined',
    },
    impairmentDescription: {
      type: 'multiline',
      name: 'impairmentDescription',
      size: 12,
      label: '',
      show: true,
      conditionWhen: 'impairment',
      conditionIs: true,
      required: false,
      variant: 'outlined',
      helperText:
        'Bitte beschreiben Sie Ihre Beeinträchtigung, damit wir Rücksicht darauf nehmen können.',
    },
    invoiceType: {
      type: 'radio',
      typeConfig: {
        radioGroupProps: {
          row: true,
        },
      },
      name: 'invoiceType',
      size: 12,
      label: 'Möchten Sie Ihre Privatadresse oder eine Firmen- oder Organisationsadresse nutzen?',
      options: [
        {
          value: 'business',
          label: 'Firmen- oder Organisationsadresse',
        },
        {
          value: 'private',
          label: 'Privatadresse',
        },
      ],
      show: true,
      required: true,
    },
    organizationName: {
      type: 'text',
      name: 'organizationName',
      size: 12,
      label: 'Organisationsname',
      show: true,
      conditionWhen: 'invoiceType',
      conditionIs: 'business',
      conditionalRequired: true,
      required: true,
      variant: 'outlined',
      invoiceType: 'business',
    },
    address: {
      type: 'text',
      name: 'address',
      size: 12,
      label: 'Straße und Hausnummer',
      show: true,
      required: true,
      variant: 'outlined',
      invoiceType: 'both',
      autocomplete: 'street-address',
    },
    zip: {
      type: 'text',
      name: 'zip',
      validation: 'numbersOnly',
      size: 4,
      label: 'PLZ',
      show: true,
      required: true,
      variant: 'outlined',
      invoiceType: 'both',
      autocomplete: 'postal-code',
    },
    city: {
      type: 'text',
      name: 'city',
      validation: 'lettersOnly',
      size: 8,
      label: 'Ort',
      show: true,
      required: true,
      variant: 'outlined',
      invoiceType: 'both',
      autocomplete: 'address-level2',
    },
    invoiceEmail: {
      type: 'email',
      name: 'invoiceEmail',
      validation: 'email',
      size: 12,
      label: 'E-Mail-Adresse',
      show: true,
      conditionWhen: 'invoiceType',
      conditionIs: 'business',
      conditionalRequired: false,
      required: false,
      variant: 'outlined',
      helperText: 'Diese Adresse wird für die Rechnungskommunikation genutzt',
      invoiceType: 'business',
      autocomplete: 'email',
    },
    isMember: {
      type: 'checkbox',
      typeConfig: {
        formGroupProps: {
          row: true,
        },
      },
      name: 'isMember',
      size: 12,
      label: 'Mitglied im Paritätischen, dies kann die Kursgebühr reduzieren',
      show: true,
      showLabel: false,
      required: false,
    },
    newsletter: {
      type: 'checkbox',
      typeConfig: {
        formGroupProps: {
          row: true,
        },
      },
      name: 'newsletter',
      size: 12,
      label: 'Ich möchte den Newsletter der Paritätischen Akademien erhalten',
      show: true,
      showLabel: false,
      required: false,
    },
    termsAndConditions: {
      type: 'checkbox',
      typeConfig: {
        formGroupProps: {
          row: true,
        },
      },
      name: 'termsAndConditions',
      size: 12,
      label: 'Ich habe die <a href="/service/allgemeine-geschaeftsbedingungen">AGBs</a> gelesen',
      show: true,
      showLabel: false,
      required: true,
    },
    dataPrivacy: {
      type: 'checkbox',
      typeConfig: {
        formGroupProps: {
          row: true,
        },
      },
      name: 'dataPrivacy',
      size: 12,
      label: 'Ich habe die <a href="/service/datenschutz">Datenschutzvereinbarung</a> gelesen',
      show: true,
      showLabel: false,
      required: true,
    },
    invoiceUseDispatch: {
      type: 'checkbox',
      typeConfig: {
        formGroupProps: {
          row: true,
        },
      },
      name: 'invoiceUseDispatch',
      size: 12,
      conditionWhen: 'invoiceType',
      conditionIs: 'business',
      label: 'Alternative Versandadresse für Rechnungen angeben',
      show: true,
      required: false,
    },
    priceType: {
      name: 'priceType',
      type: 'priceTypeSelect',
      label: 'Paritätische Mitgliedschaft',
      variant: 'outlined',
      size: 12,
      show: true,
    },
    invoiceDispatchStreet: {
      type: 'text',
      name: 'invoiceDispatchStreet',
      size: 12,
      label: 'Straße und Hausnummer',
      show: true,
      conditionWhen: 'invoiceDispatch',
      conditionIs: true,
      required: true,
      variant: 'outlined',
    },
    invoiceDispatchPostcode: {
      type: 'text',
      name: 'invoiceDispatchPostcode',
      validation: 'numbersOnly',
      size: 4,
      label: 'PLZ',
      show: true,
      conditionWhen: 'invoiceDispatch',
      conditionIs: true,
      required: true,
      variant: 'outlined',
    },
    invoiceDispatchCity: {
      type: 'text',
      name: 'invoiceDispatchCity',
      validation: 'lettersOnly',
      size: 8,
      label: 'Ort',
      show: true,
      conditionWhen: 'invoiceDispatch',
      conditionIs: true,
      required: true,
      variant: 'outlined',
    },
    fileUploadCvAndReports: {
      type: 'fileUpload',
      description:
        'Bitte laden Sie nun Ihren Lebenslauf und Ihr Hochschulzeugnis hoch. Falls Sie keinen Hochschulabschluss haben, laden Sie bitte Ihr Berufsausbilungszeugnis sowie einen Nachweis der Berufsausbildung hoch.',
      label: 'Lebenslauf und Zeugnisse hochladen',
      name: 'fileUploadCvAndReports',
      filesLimit: 5,
      maxSize: 20971520,
      acceptedFiles: ['.pdf', '.doc', '.docx'],
      size: 12,
      show: true,
      required: true,
    },
    fileUploadApplicationAndOther: {
      type: 'fileUpload',
      description:
        'Laden Sie den oben erwähnten Antrag bitte unterschrieben als Scan oder Foto wieder hoch. Falls Sie ergänzende Unterlagen haben, können Sie diese ebenfalls in diesem Schritt hochladen.',
      label: 'Anmeldeformular und sonstige Dokumente hochladen',
      name: 'fileUploadApplicationAndOther',
      filesLimit: 5,
      maxSize: 20971520,
      acceptedFiles: ['.pdf', '.doc', '.docx'],
      size: 12,
      show: true,
      required: true,
    },
    newsletterSalutation: {
      type: 'radio',
      typeConfig: {
        radioGroupProps: {
          row: true,
        },
      },
      name: 'newsletterSalutation',
      label: 'Anrede',
      size: 12,
      show: true,
      required: true,
      variant: 'outlined',
      options: [
        {
          value: 'Frau',
          label: 'Frau',
        },
        {
          value: 'Herr',
          label: 'Herr',
        },
        {
          value: 'Divers',
          label: 'Divers',
        },
      ],
    },
    newsletterEmail: {
      type: 'email',
      name: 'newsletterEmail',
      validation: 'email',
      size: 12,
      label: 'E-Mail-Adresse',
      show: true,
      required: true,
      variant: 'outlined',
      autocomplete: 'email',
    },
    prospectEmail: {
      type: 'email',
      name: 'prospectEmail',
      validation: 'email',
      size: 6,
      label: 'E-Mail-Adresse',
      show: true,
      required: true,
      variant: 'outlined',
      autocomplete: 'email',
    },
    newsletterDataPrivacy: {
      type: 'checkbox',
      typeConfig: {
        formGroupProps: {
          row: true,
        },
      },
      name: 'newsletterDataPrivacy',
      size: 12,
      label: 'Ich habe die <a href="/service/datenschutz">Datenschutzvereinbarung</a> gelesen',
      show: true,
      required: true,
    },
    srhConsent: {
      type: 'checkbox',
      typeConfig: {
        formGroupProps: {
          row: true,
        },
      },
      name: 'srhConsent',
      size: 12,
      label:
        'Hiermit erkläre ich mich einverstanden, dass meine Daten zur Beantwortung meiner Anfrage an unseren Kooperationspartner, die SRH Fernhochschule – The Mobile University, weitergegeben werden. Die SRH Fernhochschule kann direkt mit mir Kontakt aufnehmen.',
      show: true,
      required: true,
    },
  },
};
