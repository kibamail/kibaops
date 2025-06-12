import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { PageProps, Project, Workspace } from '@/types';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';

export default function Edit({ auth, workspace, project }: PageProps<{ workspace: Workspace, project: Project }>) {
    const { data, setData, patch, processing, errors } = useForm({
        name: project.name,
        slug: project.slug,
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        patch(route('workspaces.projects.update', { workspace: workspace.id, project: project.id }));
    }

    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Edit Project</h2>}
        >
            <Head title="Edit Project" />

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
                                    <InputLabel htmlFor="slug" value="Slug" />
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
                                    <PrimaryButton disabled={processing}>
                                        Update Project
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}