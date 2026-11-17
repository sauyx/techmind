import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';

export default function Dashboard() {
    return (
        <>
            <Head title="Dashboard" />
            <AppLayout>
                <div className="p-4">Dashboard</div>
            </AppLayout>
        </>
    );
}
