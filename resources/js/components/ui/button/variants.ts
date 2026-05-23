import { cva, type VariantProps } from 'class-variance-authority';

export const buttonVariants = cva(
  'inline-flex shrink-0 items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-[background-color,border-color,color,box-shadow,transform] duration-150 ease-out focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none disabled:pointer-events-none disabled:translate-y-0 disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0',
  {
    variants: {
      variant: {
        default: 'border border-primary/50 bg-primary/86 text-primary-foreground shadow-sm shadow-primary/12 hover:-translate-y-px hover:bg-primary/78 hover:shadow-primary/20 dark:border-primary/80 dark:bg-primary dark:text-primary-foreground dark:shadow-primary/15 dark:hover:bg-primary/92 dark:hover:shadow-primary/25',
        destructive: 'border border-destructive/60 bg-destructive/18 text-destructive shadow-sm shadow-destructive/10 hover:-translate-y-px hover:bg-destructive/26 hover:text-white focus-visible:ring-destructive/20',
        outline: 'border border-input bg-background/70 text-foreground shadow-sm shadow-black/5 hover:-translate-y-px hover:border-primary/55 hover:bg-accent/75 hover:text-accent-foreground',
        secondary: 'border border-secondary/80 bg-secondary text-secondary-foreground hover:-translate-y-px hover:bg-secondary/85',
        success: 'border border-emerald-600/35 bg-emerald-500/14 text-emerald-800 shadow-sm shadow-emerald-950/10 hover:-translate-y-px hover:bg-emerald-500/22 hover:text-emerald-950 focus-visible:ring-emerald-500/25 dark:border-emerald-400/45 dark:bg-emerald-500/18 dark:text-emerald-200 dark:shadow-emerald-950/15 dark:hover:bg-emerald-500/28 dark:hover:text-emerald-50 dark:focus-visible:ring-emerald-400/25',
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
