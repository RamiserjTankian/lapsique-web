import { Head, Link, router, usePage } from '@inertiajs/react';
import SiteLayout from '@/layouts/SiteLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';
import { route } from '@/lib/route';
import type { ContentBookingData, PageProps } from '@/types';
import { format, parseISO } from 'date-fns';
import { es } from 'date-fns/locale';
import { ExternalLink, FolderOpen, LogOut } from 'lucide-react';

interface PaymentRow {
    type: string;
    label: string;
    status: string;
    status_key: string;
    amount: string;
    date: string;
    detail: string | null;
}

interface TicketOrderRow {
    id: number;
    label: string;
    amount: string;
    paid_at: string | null;
    success_url: string;
}

interface CustomerPortalProps {
    customer: { id: number; name: string; email: string } | null;
    bookings: ContentBookingData[];
    ticketOrders: TicketOrderRow[];
    payments: PaymentRow[];
}

export default function CustomerPortal({ customer, bookings, ticketOrders, payments }: CustomerPortalProps) {
    const { ziggy } = usePage<PageProps>().props;

    if (!customer) {
        return (
            <SiteLayout>
                <Head title="Mi portal" />
                <div className="py-20 text-center">
                    <h1 className="font-display text-2xl font-bold">Mi portal</h1>
                    <p className="mt-4 text-muted-foreground">
                        Inicia sesión para ver tus reservas y compras.
                    </p>
                    <Button variant="cinematic" className="mt-6" asChild>
                        <Link href={route('customers.login', undefined, false, ziggy)}>
                            Iniciar sesión
                        </Link>
                    </Button>
                </div>
            </SiteLayout>
        );
    }

    const logout = () => {
        router.post(route('customers.logout', undefined, false, ziggy));
    };

    return (
        <SiteLayout>
            <Head title="Mi portal" />
            <div className="mx-auto max-w-4xl py-12">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 className="font-display text-3xl font-bold">Hola, {customer.name}</h1>
                        <p className="text-muted-foreground">{customer.email}</p>
                    </div>
                    <Button variant="outline" size="sm" onClick={logout}>
                        <LogOut className="mr-2 h-4 w-4" />
                        Cerrar sesión
                    </Button>
                </div>

                <Tabs defaultValue="sessions" className="mt-10">
                    <TabsList className="glass-panel w-full justify-start overflow-x-auto">
                        <TabsTrigger value="sessions">Mis sesiones</TabsTrigger>
                        <TabsTrigger value="purchases">Mis compras</TabsTrigger>
                        <TabsTrigger value="profile">Perfil</TabsTrigger>
                    </TabsList>

                    <TabsContent value="sessions" className="mt-6">
                        <SessionsList bookings={bookings} />
                    </TabsContent>

                    <TabsContent value="purchases" className="mt-6 space-y-8">
                        {ticketOrders.length > 0 && (
                            <section>
                                <h2 className="font-display text-lg font-semibold">Tickets</h2>
                                <div className="mt-4 grid gap-3">
                                    {ticketOrders.map((order) => (
                                        <Card key={order.id} className="glass-panel border-border">
                                            <CardHeader className="pb-2">
                                                <CardTitle className="text-base">{order.label}</CardTitle>
                                            </CardHeader>
                                            <CardContent className="flex flex-wrap items-center justify-between gap-3 text-sm text-muted-foreground">
                                                <span>{order.amount}</span>
                                                <Button variant="outline" size="sm" asChild>
                                                    <a href={order.success_url}>Ver compra</a>
                                                </Button>
                                            </CardContent>
                                        </Card>
                                    ))}
                                </div>
                            </section>
                        )}

                        <section>
                            <h2 className="font-display text-lg font-semibold">Historial de pagos</h2>
                            <PaymentsList payments={payments} />
                        </section>
                    </TabsContent>

                    <TabsContent value="profile" className="mt-6">
                        <Card className="glass-panel border-border">
                            <CardContent className="space-y-3 pt-6 text-sm">
                                <p>
                                    <span className="text-muted-foreground">Nombre: </span>
                                    {customer.name}
                                </p>
                                <p>
                                    <span className="text-muted-foreground">Email: </span>
                                    {customer.email}
                                </p>
                                <Button variant="link" className="h-auto p-0" asChild>
                                    <Link href={route('customers.password.request', undefined, false, ziggy)}>
                                        Cambiar contraseña
                                    </Link>
                                </Button>
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>
        </SiteLayout>
    );
}

function SessionsList({ bookings }: { bookings: ContentBookingData[] }) {
    if (bookings.length === 0) {
        return (
            <p className="text-muted-foreground">
                No tienes sesiones de contenido aún.
            </p>
        );
    }

    return (
        <div className="grid gap-4">
            {bookings.map((booking) => (
                <Card key={booking.public_id} className="glass-panel border-border">
                    <CardHeader className="pb-2">
                        <div className="flex flex-wrap items-center justify-between gap-2">
                            <CardTitle className="text-base">Sesión de contenido</CardTitle>
                            <Badge variant={booking.status === 'confirmed' ? 'default' : 'secondary'}>
                                {booking.status_label ?? booking.status}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent className="space-y-3 text-sm text-muted-foreground">
                        {booking.slot && (
                            <p>
                                {format(parseISO(booking.slot.date), 'd MMM yyyy', { locale: es })}{' '}
                                · {booking.slot.time_label}
                            </p>
                        )}
                        {booking.formatted_amount && <p>{booking.formatted_amount}</p>}
                        {booking.deliverable_links && booking.deliverable_links.length > 0 ? (
                            <div className="flex flex-col gap-2">
                                <p className="text-xs font-medium text-foreground">Tu contenido está listo</p>
                                {booking.deliverable_links.map((link) => (
                                    <Button key={link.id} variant="cinematic" size="sm" asChild>
                                        <a
                                            href={link.url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            <FolderOpen className="mr-2 h-4 w-4" />
                                            {link.label}
                                            <ExternalLink className="ml-2 h-3 w-3" />
                                        </a>
                                    </Button>
                                ))}
                            </div>
                        ) : booking.deliverables_ready_at && booking.deliverables_drive_url ? (
                            <Button variant="cinematic" size="sm" asChild>
                                <a
                                    href={booking.deliverables_drive_url}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <FolderOpen className="mr-2 h-4 w-4" />
                                    Abrir material en Drive
                                    <ExternalLink className="ml-2 h-3 w-3" />
                                </a>
                            </Button>
                        ) : booking.status === 'confirmed' ? (
                            <p className="text-xs">Tu material en Drive aparecerá aquí cuando el equipo lo publique.</p>
                        ) : null}
                    </CardContent>
                </Card>
            ))}
        </div>
    );
}

function PaymentsList({ payments }: { payments: PaymentRow[] }) {
    if (payments.length === 0) {
        return <p className="mt-4 text-muted-foreground">Sin pagos registrados.</p>;
    }

    return (
        <div className="mt-4 grid gap-3">
            {payments.map((payment, i) => (
                <Card key={i} className="glass-panel border-border">
                    <CardHeader className="pb-2">
                        <CardTitle className="text-base">{payment.label}</CardTitle>
                    </CardHeader>
                    <CardContent className="text-sm text-muted-foreground">
                        <p>{payment.status} · {payment.amount}</p>
                        {payment.detail && <p>{payment.detail}</p>}
                    </CardContent>
                </Card>
            ))}
        </div>
    );
}
