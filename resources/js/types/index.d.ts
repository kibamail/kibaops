import type { Config } from 'ziggy-js';

export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at?: string;
}

export interface Workspace {
  id: number;
  name: string;
  slug: string;
  user_id: number;
  created_at: string;
  updated_at: string;
  projects?: Project[];
}

export interface Project {
  id: number;
  name: string;
  slug: string;
  workspace_id: number;
  created_at: string;
  updated_at: string;
}

export interface WorkspaceMembership {
  id: number;
  workspace_id: number;
  user_id: number | null;
  email: string;
  role: 'developer' | 'admin';
  created_at: string;
  updated_at: string;
  user?: User;
  workspace?: Workspace;
  projects?: Project[];
}

export interface WorkspaceMembershipProject {
  id: number;
  workspace_membership_id: number;
  project_id: number;
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
  ziggy: Config & { location: string };
};
