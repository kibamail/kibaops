import { SignoutForm } from "./SignoutForm";
import { CheckIcon } from "@/Components/Icons/check.svg";
import { NavArrowDownIcon } from "@/Components/Icons/nav-arrow-down.svg";
import { PlusIcon } from "@/Components/Icons/plus.svg";
import { SettingsIcon } from "@/Components/Icons/settings.svg";
import { UserPlusIcon } from "@/Components/Icons/user-plus.svg";
import { UserIcon } from "@/Components/Icons/user.svg";
import { Text } from "@kibamail/owly/text";
import * as DropdownMenu from "@radix-ui/react-dropdown-menu";
import cn from "classnames";
import { usePage } from "@inertiajs/react";
import type { PageProps } from "@/types";

interface WorkspacesDropdownMenuProps {
    rootId: string;
    onCreateWorkspaceClick: () => void;
}

export function WorkspacesDropdownMenu({
    rootId,
    onCreateWorkspaceClick,
}: WorkspacesDropdownMenuProps) {
    const { workspaces, invitedWorkspaces, activeWorkspaceId } = usePage<PageProps>().props;

    // Combine user's own workspaces and invited workspaces
    const allWorkspaces = [...workspaces, ...invitedWorkspaces];

    // Find the currently active workspace
    const activeWorkspace = allWorkspaces.find(
        workspace => workspace.id.toString() === activeWorkspaceId
    ) || allWorkspaces[0]; // Fallback to first workspace if no active one is set

    return (
        <DropdownMenu.Root>
            <DropdownMenu.Trigger asChild>
                <button
                    type="button"
                    id={`${rootId}-dropdown-menu-trigger`}
                    data-testid={`${rootId}-dropdown-menu-trigger`}
                    className="grow flex items-center border transition ease-in-out border-(--border-tertiary) hover:bg-(--background-hover) focus:outline-none focus-within:border-(--border-focus) p-1 rounded-lg"
                >
                    <span className="grow flex items-center">
                        <TeamAvatar name={activeWorkspace?.name} size="md" />

                        <Text className="kb-content-primary truncate capitalize">
                            {activeWorkspace?.name || 'Select Workspace'}
                        </Text>
                    </span>

                    <NavArrowDownIcon
                        aria-hidden
                        className="ml-1 w-4 h-4 kb-content-tertiary-inverse"
                    />
                </button>
            </DropdownMenu.Trigger>

            <DropdownMenu.Content
                sideOffset={8}
                align="start"
                id={`${rootId}-dropdown-menu-content`}
                className="border workspaces-dropdown-menu kb-border-tertiary absolute rounded-xl p-1 shadow-[0px_16px_24px_-8px_var(--black-10)] kb-background-primary w-70 data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 z-50"
            >
                <DropdownMenu.RadioGroup value={activeWorkspace?.id.toString()}>
                    {allWorkspaces.map((workspace) => (
                        <DropdownMenu.RadioItem
                            key={workspace.id}
                            value={workspace.id.toString()}
                            asChild
                        >
                            <a
                                data-testid={`${rootId}-switch-workspace-id-${workspace.id}`}
                                href={route("workspaces.switch", {
                                    workspace: workspace.id,
                                })}
                                className="p-2 flex items-center hover:bg-(--background-secondary) rounded-lg cursor-pointer"
                            >
                                <TeamAvatar name={workspace.name} size="sm" />
                                <Text className="kb-content-secondary capitalize">
                                    {workspace.name}
                                </Text>

                                <DropdownMenu.ItemIndicator className="ml-auto">
                                    <CheckIcon className="w-5 h-5 kb-content-secondary" />
                                </DropdownMenu.ItemIndicator>
                            </a>
                        </DropdownMenu.RadioItem>
                    ))}
                </DropdownMenu.RadioGroup>

                <DropdownMenu.Item
                    className="p-2 flex items-center hover:bg-(--background-secondary) rounded-lg cursor-pointer"
                    onSelect={onCreateWorkspaceClick}
                >
                    <PlusIcon className="mr-1.5 w-5 h-5 kb-content-tertiary" />
                    <Text>New workspace</Text>
                </DropdownMenu.Item>

                <DropdownMenu.Separator className="my-1 h-px bg-(--black-5)" />

                <DropdownMenu.Item className="p-2 flex items-center hover:bg-(--background-secondary) rounded-lg cursor-pointer">
                    <UserPlusIcon className="mr-1.5 w-5 h-5 kb-content-tertiary" />
                    <Text>Invite member</Text>
                </DropdownMenu.Item>

                <DropdownMenu.Separator className="my-1 h-px bg-(--black-5)" />

                <DropdownMenu.Item className="p-2 flex items-center hover:bg-(--background-secondary) rounded-lg cursor-pointer">
                    <SettingsIcon className="mr-1.5 w-5 h-5 kb-content-tertiary" />
                    <Text>Team settings</Text>
                </DropdownMenu.Item>

                <DropdownMenu.Item
                    asChild
                    className="p-2 flex items-center hover:bg-(--background-secondary) rounded-lg cursor-pointer"
                >
                    <a href={"/"}>
                        <UserIcon className="mr-1.5 w-5 h-5 kb-content-tertiary" />
                        <Text>Account settings</Text>
                    </a>
                </DropdownMenu.Item>

                <DropdownMenu.Separator className="my-1 h-px bg-(--black-5)" />

                <SignoutForm />
            </DropdownMenu.Content>
        </DropdownMenu.Root>
    );
}

interface TeamAvatarProps {
    size: "sm" | "md";
    name?: string;
}

function TeamAvatar({ size, name }: TeamAvatarProps) {
    return (
        <span
            className={cn(
                "mr-1.5 text-sm shadow-[0px_0px_0px_1px_rgba(0,0,0,0.10)_inset] kb-background-info rounded-lg flex items-center justify-center kb-content-primary-inverse uppercase",
                {
                    "w-5 h-5": size === "sm",
                    "w-6 h-6": size === "md",
                },
                getTeamAvatarBackgroundColor(name?.[0] ?? "")
            )}
        >
            {name?.[0]}
        </span>
    );
}

function getTeamAvatarBackgroundColor(firstCharacter: string) {
    const colors = [
        "kb-background-info",
        "kb-background-positive",
        "kb-background-negative",
        "kb-background-warning",
        "kb-background-highlight",
    ];

    const asciiValue = firstCharacter.charCodeAt(0);
    const index = (asciiValue - 97) % colors.length;

    return colors[index] ?? colors?.[0];
}
