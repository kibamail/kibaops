import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import type { PageProps, Workspace } from '@/types';
import { Head, useForm } from '@inertiajs/react';

export default function Create({ auth, workspace }: PageProps<{ workspace: Workspace }>) {
  const { data, setData, post, processing, errors } = useForm({
    name: '',
    slug: '',
    workspace_id: workspace.id,
  });

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    post(route('workspaces.projects.store', workspace.id));
  }

  return (
    <AuthenticatedLayout
      header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Create Project</h2>}
    >
      <Head title="Create Project" />

      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div className="p-6 text-gray-900">
              <form onSubmit={handleSubmit} className="max-w-xl">
                <div className="mt-4">
                  <InputLabel htmlFor="name" value="Name" />
                  <TextInput
                    id="name"
                    type="text"
                    name="name"
                    value={data.name}
                    className="mt-1 block w-full"
                    onChange={(e) => setData('name', e.target.value)}
                    required
                  />
                  <InputError message={errors.name} className="mt-2" />
                </div>

                <div className="mt-4">
                  <InputLabel htmlFor="slug" value="Slug (optional)" />
                  <TextInput
                    id="slug"
                    type="text"
                    name="slug"
                    value={data.slug}
                    className="mt-1 block w-full"
                    onChange={(e) => setData('slug', e.target.value)}
                  />
                  <InputError message={errors.slug} className="mt-2" />
                </div>

                <div className="mt-6">
                  <PrimaryButton disabled={processing}>Create Project</PrimaryButton>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
