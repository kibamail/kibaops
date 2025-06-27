import { CreateProjectFlow } from "@/Components/Dashboard/CreateProjectFlow";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import type { PageProps } from "@/types";
import { Head, usePage } from "@inertiajs/react";
import { useState } from "react";
import { NoCloudProviders } from "./Components/NoCloudProviders";
import { NoSourceProviders } from "./Components/NoSourceProviders";
import { NoWorkspaceCluster } from "./Components/NoWorkspaceCluster";
import { NoWorkspaceProjects } from "./Components/NoWorkspaceProjects";

export default function Dashboard() {
    const {
        cloudProvidersCount,
        sourceCodeConnectionsCount,
        clustersCount,
        projects,
    } = usePage<PageProps>().props;

    const [isCreateProjectOpen, setIsCreateProjectOpen] = useState(false);

    function onCreateProjectClick () {
        setIsCreateProjectOpen(true);
    };

    const dashboardStates = [
        {
            condition: cloudProvidersCount === 0,
            component: <NoCloudProviders />,
        },
        {
            condition: cloudProvidersCount > 0 && clustersCount === 0,
            component: <NoWorkspaceCluster />,
        },
        {
            condition:
                cloudProvidersCount > 0 &&
                clustersCount > 0 &&
                sourceCodeConnectionsCount === 0,
            component: <NoSourceProviders />,
        },
        {
            condition:
                cloudProvidersCount > 0 &&
                sourceCodeConnectionsCount > 0 &&
                clustersCount > 0 &&
                projects.length === 0,
            component: (
                <NoWorkspaceProjects
                    onCreateProjectClick={onCreateProjectClick}
                />
            ),
        },
    ];

    console.log({clustersCount, cloudProvidersCount})

    const activeState = dashboardStates.find((state) => state.condition);

    return (
        <>
            <AuthenticatedLayout>
                <Head title="Dashboard" />
                <div className="w-full h-full">{activeState?.component}</div>
            </AuthenticatedLayout>

            <CreateProjectFlow
                isOpen={isCreateProjectOpen}
                onOpenChange={setIsCreateProjectOpen}
            />
        </>
    );
}
