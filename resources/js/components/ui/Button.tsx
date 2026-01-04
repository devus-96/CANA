// components/ui/Button.tsx
import { cn } from '@/lib/utils'
import { Loader2 } from 'lucide-react'
import React, { forwardRef } from 'react'

export interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  color?: 'default' | 'secondary' | 'green' | 'red'  | 'light' | 'dark' | 'outline' | 'neutral'
  loading?: boolean;
}

const baseClasses =
  ''

const colorClasses: Record<NonNullable<ButtonProps['color']>, string> = {
  default:
    'text-white bg-red-500 hover:bg-primary-600 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 cursor-pointer',
  secondary: "text-white bg-secondary-500 hover:bg-secondary-600 focus:ring-secondary-300",
  neutral: "text-white bg-gray-500 hover:bg-gray-600 focus:ring-dray-300",
  outline: "text-primary-600 border-2 border-primary-600",
  green:
    'text-white bg-green-700 hover:bg-green-800 focus:ring-green-300 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800',
  red:
    'text-white bg-red-700 hover:bg-red-800 focus:ring-red-300 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900',
  light:
    'text-gray-900 bg-white border border-gray-300 hover:bg-gray-100 focus:ring-gray-100 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700',
  dark:
    'text-white bg-gray-800 hover:bg-gray-900 focus:ring-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 dark:focus:ring-gray-700 dark:border-gray-700',
}




export const Button = forwardRef<HTMLButtonElement,ButtonProps>( ({
    children,
    color = 'default',
    className = '',
    loading = false,
    ...props
  },ref) => {
    return (
        <button
        ref={ref}
          className={cn('text-sm font-medium rounded-lg px-5 py-2.5 me-2 mb-2 focus:outline-none focus:ring-4 inline-flex justify-center rounded-full',colorClasses[color], className)}
          {...props}
        >
             {loading && <Loader2 className="animate-spin mr-2" />}
          {children}
        </button>
      )
})


Button.displayName = 'Button'
