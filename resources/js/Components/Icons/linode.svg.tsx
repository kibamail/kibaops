import React from 'react';

export const LinodeIcon = React.forwardRef<
  React.ElementRef<'svg'>,
  React.ComponentPropsWithoutRef<'svg'>
>((props, forwardedRef) => {
  return (
    <svg
      width="32px"
      height="32px"
      viewBox="0 0 1024 1024"
      xmlns="http://www.w3.org/2000/svg"
      {...props}
      ref={forwardedRef}
    >
      <title>Linode logo</title>
      <circle cx="512" cy="512" r="512" style={{ fill: '#00b050' }} />
      <path
        d="m737.51 482.63-71.12-39.05-59.74 36.49-.74 37.27-29.11-19.18-39.6 24.26c2.64 62.35 2.29 59.9 2.52 59.9l-97.24 65.49-15.53-104.54 108.31-61.76-40.68-27.17-75.08 38.81-21-143.36 129.12-49.53-91.9-44.26-125.23 39.05 27.87 134.75 42 32.57-31.83 15.1 21 102 29.31 27.48-21 12.73 16.23 78.88L459.83 768c-12-81.21-11.65-78.65-11.45-78.65v-.23L526 633.76c17.16-12.23 15.18-10.83 15.53-10.83l1 24.53 33.77 28.3-.85-77.64 46.58-33.39c14.4-10.25 26.63-18.94 26.75-18.94l-2.17 36 25.16 17.47 6.6-74.69z"
        style={{ fill: '#fff' }}
      />
    </svg>
  );
});
