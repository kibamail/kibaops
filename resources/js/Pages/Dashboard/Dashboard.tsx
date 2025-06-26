import { CreateProjectFlow } from "@/Components/Dashboard/CreateProjectFlow";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import type { PageProps } from "@/types";
import { Head, usePage } from "@inertiajs/react";
import { useState } from "react";
import { NoCloudProviders } from "./Components/NoCloudProviders";
import { NoSourceProviders } from "./Components/NoSourceProviders";
import { NoWorkspaceProjects } from "./Components/NoWorkspaceProjects";

export default function Dashboard() {
    const { cloudProvidersCount, sourceCodeConnectionsCount } =
        usePage<PageProps>().props;

    const [isCreateProjectOpen, setIsCreateProjectOpen] = useState(false);

    const handleCreateProjectClick = () => {
        setIsCreateProjectOpen(true);
    };

    return (
        <>
            <AuthenticatedLayout>
                <Head title="Dashboard" />

                <div className="w-full h-full">
                    {cloudProvidersCount === 0 && <NoCloudProviders />}
                    {cloudProvidersCount > 0 &&
                        sourceCodeConnectionsCount === 0 && <NoSourceProviders />}
                    <NoWorkspaceProjects onCreateProjectClick={handleCreateProjectClick} />
                </div>
            </AuthenticatedLayout>

            <CreateProjectFlow isOpen={isCreateProjectOpen} onOpenChange={setIsCreateProjectOpen} />
        </>
    );
}
