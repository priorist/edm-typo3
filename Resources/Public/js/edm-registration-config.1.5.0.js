window.EdmForm = window.EdmForm || {};

window.EdmForm.config = {
  translations: {
    de: {
      translation: {
        fields: {
          attendees: {
            subHeadline: "Weitere Teilnehmer*innen",
          },
        },
        stepsForLoggedInUsers: {
          attendee: {
            headline: "Zusätzliche Angaben",
            buttonLabel: "Weiter",
          },
        },
      },
    },
    en: {
      translation: {
        fields: {
          attendees: {
            subHeadline: "Additional attendees",
          },
        },
        stepsForLoggedInUsers: {
          attendee: {
            headline: "Additional data",
            buttonLabel: "Next",
          },
        },
      },
    },
  },
  settings: {
    mode: "singleStep",
    enableDebug: false,
    showProgressBar: false,
    edm: {
      baseURL: "https://stage.education-manager.de",
      apiPath: "/api/v1",
      auth: {
        clientId: "",
        redirectUri: "/anmeldung",
      },
      prices: [],
      showPriceOnlyForNonPrivate: false,
      useRegistrationBegin: true,
      useRegistrationDeadline: true,
    },
    newsletter: {
      baseURL: "",
    },
    behaviour: {
      fadeInTimeout: 500,
      generateIcsFile: false,
      showFileSize: false,
      scrollToNextStep: false,
    },
    error: {
      messageUrl: "/edm-anmeldung-fehler",
    },
  },
  texts: {
    addToCalendar: "texts.addToCalendar",
    errorMessages: {
      required: "texts.errorMessages.required",
      email: "texts.errorMessages.email",
      letters: "texts.errorMessages.letters",
      numbers: "texts.errorMessages.numbers",
      fileUpload: "texts.errorMessages.fileUpload",
      birthday: "texts.errorMessages.birthday",
      date: "texts.errorMessages.date",
    },
    fileUpload: {
      introRegistrationForm: "texts.fileUpload.introRegistrationForm",
      description: "texts.fileUpload.description",
      files: "texts.fileUpload.files",
      error: "texts.fileUpload.error",
      uploading: "texts.fileUpload.uploading",
      maxFiles: "texts.fileUpload.maxFiles",
      maxSize: "texts.fileUpload.maxSize",
    },
    notBookable: {
      headline: "texts.notBookable.headline",
      text: "texts.notBookable.text",
    },
    newsletter: {
      description: "texts.newsletter.description",
    },
    login: {
      loginButton: "texts.login.loginButton",
      registerButton: "texts.login.registerButton",
      guestHeadline: "texts.login.guestHeadline",
      guestIntro: "texts.login.guestIntro",
      guestButton: "texts.login.guestButton",
    },
  },
  formTypes: [
    {
      name: "default",
      disableEdmLogin: false,
      allowMultipleAttendees: true,
      stepsForLoggedInUsers: [
        {
          headline: "formTypes.default.stepsForLoggedInUsers.attendee.headline",
          buttonLabel: "formTypes.default.stepsForLoggedInUsers.attendee.buttonLabel",
          fields: ["birthday"],
        },
        {
          headline: "formTypes.default.stepsForLoggedInUsers.enrollment.headline",
          buttonLabel: "formTypes.default.stepsForLoggedInUsers.enrollment.buttonLabel",
          fields: ["priceType", "newsletter"],
          texts: {
            cancellationRight:
              "formTypes.default.stepsForLoggedInUsers.enrollment.texts.cancellationRight",
          },
        },
      ],
      steps: [
        {
          headline: "formTypes.default.steps.attendee.headline",
          buttonLabel: "formTypes.default.steps.attendee.buttonLabel",
          fields: ["attendees"],
          texts: {
            addAttendee: "formTypes.default.steps.attendee.texts.addAttendee",
            removeAttendee: "formTypes.default.steps.attendee.texts.removeAttendee",
          },
        },
        {
          headline: "formTypes.default.steps.invoice.headline",
          buttonLabel: "formTypes.default.steps.invoice.buttonLabel",
          fields: [
            "invoiceType",
            "organizationName",
            "invoiceAddress",
            "invoiceZip",
            "invoiceCity",
            "invoiceEmail",
          ],
        },
        {
          headline: "formTypes.default.steps.enrollment.headline",
          buttonLabel: "formTypes.default.steps.enrollment.buttonLabel",
          fields: ["priceType", "note", "termsAndConditions", "dataPrivacy"],
          texts: {
            cancellationRight: "formTypes.default.steps.enrollment.texts.cancellationRight",
          },
        },
      ],
      formSuccess: {
        headline: "formTypes.default.formSuccess.headline",
        text: "formTypes.default.formSuccess.text",
      },
      formError: {
        headline: "formTypes.default.formError.headline",
        text: "formTypes.default.formError.text",
      },
      buttonLabel: "formTypes.default.buttonLabel",
      defaults: {
        invoiceType: "private",
      },
    },
    {
      name: "newsletterList",
      type: "list",
      lists: [1],
      disableEdmLogin: false,
      steps: [
        {
          headline: "formTypes.list.steps.registration.headline",
          buttonLabel: "formTypes.list.steps.registration.buttonLabel",
          fields: [
            "listSalutation",
            "listFirstName",
            "listLastName",
            "listEmail",
            "listDataPrivacy",
          ],
        },
      ],
      formSuccess: {
        headline: "formTypes.default.formSuccess.headline",
        text: "formTypes.default.formSuccess.text",
      },
      formError: {
        headline: "formTypes.default.formError.headline",
        text: "formTypes.default.formError.text",
      },
      buttonLabel: "formTypes.default.buttonLabel",
    },
  ],
  fields: [
    {
      type: "array",
      key: "attendees",
      subHeadline: "fields.attendees.subHeadline",
      sizes: [12, 6, 6, 12],
      fields: [
        {
          key: "salutation",
          type: "radio",
          typeConfig: {
            radioGroupProps: {
              row: true,
            },
          },
          required: true,
          variant: "outlined",
          options: [
            {
              value: "F",
              label: "fields.salutation.options.female",
            },
            {
              value: "M",
              label: "fields.salutation.options.male",
            },
            {
              value: "NON_BINARY",
              label: "fields.salutation.options.nonBinary",
            },
          ],
        },
        {
          key: "firstName",
          type: "text",
          validation: "letters",
          required: true,
          variant: "outlined",
          autocomplete: "given-name",
        },
        {
          key: "lastName",
          type: "text",
          validation: "letters",
          required: true,
          variant: "outlined",
          autocomplete: "family-name",
        },
        {
          key: "email",
          type: "email",
          validation: "email",
          required: true,
          variant: "outlined",
          autocomplete: "email",
        },
      ],
    },
    {
      type: "radio",
      typeConfig: {
        radioGroupProps: {
          row: true,
        },
      },
      key: "salutation",
      size: 12,
      required: true,
      variant: "outlined",
      options: [
        {
          value: "F",
          label: "fields.salutation.options.female",
        },
        {
          value: "M",
          label: "fields.salutation.options.male",
        },
        {
          value: "NON_BINARY",
          label: "fields.salutation.options.nonBinary",
        },
      ],
    },
    {
      type: "text",
      key: "firstName",
      validation: "letters",
      size: 6,
      required: true,
      variant: "outlined",
      autocomplete: "given-name",
    },
    {
      type: "text",
      key: "lastName",
      validation: "letters",
      size: 6,
      required: true,
      variant: "outlined",
      autocomplete: "family-name",
    },
    {
      type: "text",
      key: "phone",
      validation: "numbers",
      size: 12,
      required: true,
      variant: "outlined",
      autocomplete: "tel",
    },
    {
      type: "email",
      key: "email",
      validation: "email",
      size: 12,
      required: true,
      variant: "outlined",
      autocomplete: "email",
    },
    {
      type: "radio",
      typeConfig: {
        radioGroupProps: {
          row: true,
        },
      },
      key: "invoiceType",
      size: 12,
      options: [
        {
          value: "business",
          label: "fields.invoiceType.options.business",
        },
        {
          value: "private",
          label: "fields.invoiceType.options.private",
        },
      ],
      required: true,
    },
    {
      type: "text",
      key: "organizationName",
      size: 12,
      conditionWhen: "invoiceType",
      conditionIs: "business",
      conditionalRequired: true,
      required: true,
      variant: "outlined",
      invoiceType: "business",
    },
    {
      type: "text",
      key: "invoiceAddress",
      size: 12,
      required: true,
      variant: "outlined",
      invoiceType: "both",
      autocomplete: "street-address",
    },
    {
      type: "text",
      key: "address",
      size: 12,
      required: true,
      variant: "outlined",
      invoiceType: "both",
      autocomplete: "street-address",
    },
    {
      type: "text",
      key: "invoiceZip",
      validation: "numbers",
      size: 4,
      required: true,
      variant: "outlined",
      invoiceType: "both",
      autocomplete: "postal-code",
    },
    {
      type: "text",
      key: "zip",
      validation: "numbers",
      size: 4,
      required: true,
      variant: "outlined",
      invoiceType: "both",
      autocomplete: "postal-code",
    },
    {
      type: "text",
      key: "invoiceCity",
      validation: "letters",
      size: 8,
      required: true,
      variant: "outlined",
      invoiceType: "both",
      autocomplete: "address-level2",
    },
    {
      type: "text",
      key: "city",
      validation: "letters",
      size: 8,
      required: true,
      variant: "outlined",
      invoiceType: "both",
      autocomplete: "address-level2",
    },
    {
      type: "email",
      key: "invoiceEmail",
      validation: "email",
      size: 12,
      conditionWhen: "invoiceType",
      conditionIs: "business",
      conditionalRequired: true,
      required: true,
      variant: "outlined",
      invoiceType: "business",
      autocomplete: "email",
    },
    {
      type: "multiline",
      key: "note",
      size: 12,
      required: false,
      variant: "outlined",
    },
    {
      type: "checkbox",
      typeConfig: {
        formGroupProps: {
          row: true,
        },
      },
      key: "termsAndConditions",
      size: 12,
      showLabel: false,
      required: true,
      acceptedConsentScopes: ["AGB"],
    },
    {
      type: "checkbox",
      typeConfig: {
        formGroupProps: {
          row: true,
        },
      },
      key: "dataPrivacy",
      size: 12,
      showLabel: false,
      required: true,
      acceptedConsentScopes: ["DATA_PRIVACY"],
    },
    {
      key: "priceType",
      type: "priceTypeSelect",
      variant: "outlined",
      size: 12,
      required: true,
    },
    {
      key: "salutation",
      name: "listSalutation",
      type: "radio",
      typeConfig: {
        radioGroupProps: {
          row: true,
        },
      },
      size: 12,
      required: false,
      variant: "outlined",
      options: [
        {
          value: "F",
          label: "fields.salutation.options.female",
        },
        {
          value: "M",
          label: "fields.salutation.options.male",
        },
        {
          value: "NON_BINARY",
          label: "fields.salutation.options.nonBinary",
        },
      ],
    },
    {
      key: "firstName",
      name: "listFirstName",
      type: "text",
      validation: "letters",
      size: 6,
      required: false,
      variant: "outlined",
      autocomplete: "given-name",
    },
    {
      key: "lastName",
      name: "listLastName",
      type: "text",
      validation: "letters",
      size: 6,
      required: false,
      variant: "outlined",
      autocomplete: "family-name",
    },
    {
      key: "email",
      name: "listEmail",
      type: "email",
      validation: "email",
      size: 12,
      required: true,
      variant: "outlined",
      autocomplete: "email",
    },
    {
      key: "listDataPrivacy",
      type: "checkbox",
      typeConfig: {
        formGroupProps: {
          row: true,
        },
      },
      size: 12,
      required: true,
    },
  ],
};
