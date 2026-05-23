import { z } from 'zod';

export type FormControlElement = HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement;

export type FieldValidator = (
  value: string,
  context: {
    control: FormControlElement;
    form: HTMLFormElement | null;
  },
) => string | undefined;

export function combineValidators(...validators: FieldValidator[]): FieldValidator {
  return (value, context) => {
    for (const validator of validators) {
      const message = validator(value, context);

      if (message) {
        return message;
      }
    }

    return undefined;
  };
}

export function optionalValidator(validator: FieldValidator): FieldValidator {
  return (value, context) => {
    if (value.trim() === '') {
      return undefined;
    }

    return validator(value, context);
  };
}

export function zodFieldValidator(schema: z.ZodType<string>): FieldValidator {
  return (value) => {
    const result = schema.safeParse(value);

    if (result.success) {
      return undefined;
    }

    return result.error.issues[0]?.message ?? 'Check this field.';
  };
}

export function matchesFieldValidator(fieldName: string, message: string): FieldValidator {
  return (value, context) => {
    const field = context.form?.elements.namedItem(fieldName) ?? null;

    if (!isFormControlElement(field)) {
      return undefined;
    }

    if (value === field.value) {
      return undefined;
    }

    return message;
  };
}

export const loginUsernameValidator = zodFieldValidator(
  z.string().trim().min(1, 'Enter your username.').max(20, 'Use 20 characters or fewer.'),
);

export const loginPasswordValidator = zodFieldValidator(
  z.string().min(1, 'Enter your password.').max(128, 'Use 128 characters or fewer.'),
);

export const usernameValidator = zodFieldValidator(
  z.string()
    .trim()
    .min(3, 'Use at least 3 characters.')
    .max(20, 'Use 20 characters or fewer.')
    .regex(/^[A-Za-z0-9_-]+$/, 'Use letters, numbers, dashes, or underscores.'),
);

export const repositoryNameValidator = zodFieldValidator(
  z.string()
    .trim()
    .min(1, 'Enter a repository name.')
    .max(100, 'Use 100 characters or fewer.')
    .regex(/^[A-Za-z0-9_-]+$/, 'Use letters, numbers, dashes, or underscores.'),
);

export const personNameValidator = zodFieldValidator(
  z.string().trim().min(1, 'Enter a name.').max(255, 'Use 255 characters or fewer.'),
);

export const emailValidator = zodFieldValidator(
  z.string().trim().min(1, 'Enter an email address.').email('Enter a valid email address.').max(255, 'Use 255 characters or fewer.'),
);

export const passwordValidator = zodFieldValidator(
  z.string()
    .min(12, 'Use at least 12 characters.')
    .max(128, 'Use 128 characters or fewer.')
    .regex(/[A-Za-z]/, 'Use at least one letter.')
    .regex(/[0-9]/, 'Use at least one number.'),
);

export const optionalPasswordValidator = optionalValidator(passwordValidator);

export const passwordConfirmationValidator = combineValidators(
  passwordValidator,
  matchesFieldValidator('password', 'Passwords need to match.'),
);

export const optionalPasswordConfirmationValidator = optionalValidator(
  matchesFieldValidator('password', 'Passwords need to match.'),
);

export function maxLengthValidator(max: number, label: string): FieldValidator {
  return zodFieldValidator(z.string().trim().min(1, `Enter ${label}.`).max(max, `Use ${max.toLocaleString()} characters or fewer.`));
}

export const optionalBioValidator = optionalValidator(
  zodFieldValidator(z.string().max(500, 'Use 500 characters or fewer.')),
);

function isFormControlElement(value: Element | RadioNodeList | null): value is FormControlElement {
  return value instanceof HTMLInputElement || value instanceof HTMLSelectElement || value instanceof HTMLTextAreaElement;
}
