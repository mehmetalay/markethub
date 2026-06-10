import { Head, usePage } from '@inertiajs/react';
import AdminLayout from '../Layouts/AdminLayout';
import type { PageProps } from '../types';

type DashboardProps = PageProps<{
    summary: {
        tenantStatus: string | null;
        roles: string[];
        apiFirst: boolean;
        marketplaceProviders: number;
    };
}>;

export default function Dashboard() {
    const { auth, summary } = usePage<DashboardProps>().props;

    return (
        <AdminLayout title="Dashboard">
            <Head title="Dashboard" />

            <div className="grid gap-4 md:grid-cols-4">
                <Metric label="Tenant" value={auth.tenant?.name ?? 'Unassigned'} />
                <Metric label="Status" value={summary.tenantStatus ?? 'n/a'} />
                <Metric label="Roles" value={summary.roles.join(', ') || 'none'} />
                <Metric label="Providers" value={String(summary.marketplaceProviders)} />
            </div>

            <section className="mt-8 rounded-lg border border-slate-200 bg-white p-6">
                <h2 className="text-lg font-semibold">Platform foundation</h2>
                <div className="mt-4 grid gap-3 text-sm text-slate-600 md:grid-cols-2">
                    <p>Single database multi-tenancy is represented by `tenant_id`.</p>
                    <p>Marketplace integrations are contract-only until provider implementation starts.</p>
                    <p>Sanctum is installed for API-first authenticated endpoints.</p>
                    <p>Spatie Permission is configured with tenant-aware teams.</p>
                </div>
            </section>
        </AdminLayout>
    );
}

function Metric({ label, value }: { label: string; value: string }) {
    return (
        <div className="rounded-lg border border-slate-200 bg-white p-5">
            <p className="text-sm text-slate-500">{label}</p>
            <p className="mt-2 truncate text-xl font-semibold text-slate-950">{value}</p>
        </div>
    );
}
