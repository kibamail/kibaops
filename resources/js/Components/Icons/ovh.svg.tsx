import React from 'react';

export const OVHIcon = React.forwardRef<
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
      <title>OVH logo</title>
      <circle cx="512" cy="512" r="512" style={{ fill: '#000e9c' }} />
      <path
        d="m700.2 466.5 61.2-106.3c23.6 41.6 37.2 89.8 37.2 141.1 0 68.8-24.3 131.9-64.7 181.4H575.8l48.7-84.6h-64.4l75.8-131.7 64.3.1zm-55.4-125.2L448.3 682.5l.1.2H290.1c-40.5-49.5-64.7-112.6-64.7-181.4 0-51.4 13.6-99.6 37.3-141.3l102.5 178.2 113.3-197h166.3z"
        style={{ fill: '#fff' }}
      />
    </svg>
  );
});
