import { createInertiaApp } from '@inertiajs/react';
import createServer from '@inertiajs/react/server';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import ReactDOMServer from 'react-dom/server';
import type { RouteName } from 'ziggy-js';
import { route } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createServer((page) =>
  createInertiaApp({
    page,
    render: ReactDOMServer.renderToString,
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
      resolvePageComponent(`./Pages/${name}.tsx`, import.meta.glob('./Pages/**/*.tsx')),
    setup: ({ App, props }) => {
      /* eslint-disable */
      // Add route function to global namespace for Inertia
      // @ts-ignore - global.route is not typed in the global namespace
      global.route = (name: string, params?: any, absolute?: boolean) =>
        // @ts-ignore - params type is complex to match exactly
        route(name, params, absolute, {
          ...page.props.ziggy,
          location: new URL(page.props.ziggy.location),
        });
      /* eslint-enable */

      return <App {...props} />;
    },
  })
);
