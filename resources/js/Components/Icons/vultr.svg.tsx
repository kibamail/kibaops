import React from 'react';

export const VultrIcon = React.forwardRef<
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
      <title>Vultr logo</title>
      <circle cx="512" cy="512" r="512" style={{ fill: '#007bfc' }} />
      <path
        d="M259.9 357.4c-2.5-3.9-3.9-8.6-3.9-13.6 0-14.1 11.5-25.6 25.6-25.6h131.1c9.1 0 17.1 4.8 21.7 12l181.9 288.5c2.5 4 3.9 8.6 3.9 13.6s-1.5 9.7-3.9 13.6l-65.6 104c-4.5 7.2-12.5 12-21.7 12-9.1 0-17.1-4.8-21.7-12L259.9 357.4zm395.3 158.1c4.5 7.2 12.5 11.9 21.7 11.9 9.1 0 17.1-4.8 21.7-11.9l22.6-35.8 43-68.2c2.5-3.9 3.9-8.6 3.9-13.7 0-5-1.5-9.7-3.9-13.7L730.1 330c-4.5-7.2-12.5-12-21.7-12H577.1c-14.1 0-25.6 11.5-25.6 25.6 0 5 1.4 9.7 3.9 13.6l99.8 158.3z"
        style={{ fill: '#fff' }}
      />
    </svg>
  );
});
