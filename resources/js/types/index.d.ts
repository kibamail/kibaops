import type { Config } from 'ziggy-js';

export interface User {
  id: string;
  name: string;
  email: string;
  email_verified_at?: string;
}

export interface Workspace {
  id: string;
  name: string;
  slug: string;
  user_id: string;
  created_at: string;
  updated_at: string;
  projects?: Project[];
}

export interface Project {
  id: string;
  name: string;
  slug: string;
  workspace_id: string;
  created_at: string;
  updated_at: string;
  environments?: Environment[];
}

export interface Environment {
  id: string;
  slug: string;
  project_id: string;
  created_at: string;
  updated_at: string;
}

export interface WorkspaceMembership {
  id: string;
  workspace_id: string;
  user_id: string | null;
  email: string;
  role: 'developer' | 'admin';
  created_at: string;
  updated_at: string;
  user?: User;
  workspace?: Workspace;
  projects?: Project[];
}

export interface WorkspaceMembershipProject {
  id: string;
  workspace_membership_id: string;
  project_id: string;
  created_at: string;
  updated_at: string;
}

export type CloudProviderType =
  | 'aws'
  | 'hetzner'
  | 'leaseweb'
  | 'google_cloud'
  | 'digital_ocean'
  | 'linode'
  | 'vultr'
  | 'ovh';

export interface CloudProvider {
  id: string;
  name: string;
  type: CloudProviderType;
  workspace_id: string;
  created_at: string;
  updated_at: string;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
  auth: {
    user: User;
  };
  workspaces: Workspace[];
  invitedWorkspaces: Workspace[];
  activeWorkspaceId: string | null;
  projects: Project[];
  activeProject: Project | null;
  cloudProvidersCount: number;
  ziggy: Config & { location: string };
};
