export type AuthUser = {
    id: number;
    name: string;
    email: string;
    tenant_id: number | null;
    roles: string[];
};

export type AuthTenant = {
    id: number;
    name: string;
    slug: string;
    status: string;
};

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: AuthUser | null;
        tenant: AuthTenant | null;
    };
};
