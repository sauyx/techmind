import { Head } from '@inertiajs/react';
import AdminLayout from '@/layouts/admin-layout';

export default function AdminDashboard() {
    return (
        <>
            <Head title="Dashboard Admin" />
            <AdminLayout>
                <h1 className="text-2xl font-semibold tracking-tight">Dashboard Admin</h1>
                <p className="mt-2 text-muted-foreground">Placeholder dashboard admin.</p>
            </AdminLayout>
        </>
    );
}
