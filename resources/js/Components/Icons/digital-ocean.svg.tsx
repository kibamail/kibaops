import React from 'react';

export const DigitalOceanIcon = React.forwardRef<
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
      <title>Digital Ocean logo</title>
      <circle cx={512} cy={512} r={512} style={{ fill: '#0080ff' }} />
      <path
        d="m273.8 669.2-.1-63.7h63.7v63.7h76v-98.8h98.8v98.5c105.1-.1 186.2-104.1 146.1-214.6-14.9-40.9-47.6-73.6-88.5-88.4-110.7-40.2-214.7 41.2-214.7 146.3H256c0-167.5 161.8-298 337.4-243.2 76.8 24 137.7 84.9 161.6 161.6C809.9 606.2 679.4 768 511.9 768v-98.8h-98.6v75.9h-75.9v-75.9h-63.6z"
        style={{ fill: '#fff' }}
      />
    </svg>
  );
});
