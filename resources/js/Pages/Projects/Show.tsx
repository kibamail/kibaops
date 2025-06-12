import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { PageProps, Project, Workspace } from '@/types';

export default function Show({ auth, workspace, project }: PageProps<{ workspace: Workspace, project: Project }>) {
    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Project Details</h2>}
        >
            <Head title={`Project: ${project.name}`} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="mb-6">
                                <Link
                                    href={route('workspaces.projects.index', workspace.id)}
                                    className="text-blue-500 hover:underline"
                                >
                                    ← Back to Projects
                                </Link>
                            </div>

                            <div className="flex justify-between items-center mb-6">
                                <h1 className="text-2xl font-semibold">{project.name}</h1>
                                <div className="flex space-x-2">
                                    <Link
                                        href={route('workspaces.projects.edit', { workspace: workspace.id, project: project.id })}
                                        className="px-4 py-2 bg-yellow-500 text-white rounded-md"
                                    >
                                        Edit
                                    </Link>
                                    <Link
                                        href={route('workspaces.projects.destroy', { workspace: workspace.id, project: project.id })}
                                        method="delete"
                                        as="button"
                                        className="px-4 py-2 bg-red-500 text-white rounded-md"
                                    >
                                        Delete
                                    </Link>
                                </div>
                            </div>

                            <div className="mt-4">
                                <p><strong>Slug:</strong> {project.slug}</p>
                                <p><strong>Created:</strong> {new Date(project.created_at).toLocaleString()}</p>
                                <p><strong>Updated:</strong> {new Date(project.updated_at).toLocaleString()}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}