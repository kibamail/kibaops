import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage } from '@inertiajs/react';
import type { PageProps } from '@/types';
import { NoCloudProviders } from './Components/NoCloudProviders';
import { NoSourceProviders } from './Components/NoSourceProviders';

export default function Dashboard() {
  const { cloudProvidersCount } = usePage<PageProps>().props;

  return (
    <AuthenticatedLayout
    >
      <Head title="Dashboard" />

      <div className="w-full h-full">
        {cloudProvidersCount === 0 && <NoCloudProviders />}
        <NoSourceProviders />
      </div>
    </AuthenticatedLayout>
  );
}
