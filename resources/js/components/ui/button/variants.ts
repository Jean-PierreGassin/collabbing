import { cva, type VariantProps } from 'class-variance-authority';

export const buttonVariants = cva(
  'inline-flex shrink-0 items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-[background-color,border-color,color,box-shadow,transform] duration-150 ease-out focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none disabled:pointer-events-none disabled:translate-y-0 disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0',
  {
    variants: {
      variant: {
        default: 'border border-primary/80 bg-primary text-primary-foreground shadow-sm shadow-primary/15 hover:-translate-y-px hover:bg-primary/92 hover:shadow-primary/25',
        destructive: 'border border-destructive/80 bg-destructive text-white shadow-sm shadow-destructive/15 hover:-translate-y-px hover:bg-destructive/90 focus-visible:ring-destructive/20',
        outline: 'border border-input bg-background/70 text-foreground shadow-sm shadow-black/5 hover:-translate-y-px hover:border-primary/55 hover:bg-accent/75 hover:text-accent-foreground',
        secondary: 'border border-secondary/80 bg-secondary text-secondary-foreground hover:-translate-y-px hover:bg-secondary/85',
        ghost: 'text-muted-foreground hover:bg-accent/70 hover:text-accent-foreground',
        link: 'text-primary underline-offset-4 hover:underline',
      },
      size: {
        default: 'h-10 px-4 py-2',
        sm: 'h-9 rounded-md px-3',
        lg: 'h-11 rounded-md px-6',
        icon: 'size-10',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'default',
    },
  },
);

export type ButtonVariants = VariantProps<typeof buttonVariants>;
