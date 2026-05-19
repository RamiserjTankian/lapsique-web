import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { route } from '@/lib/route';
import { Link, usePage } from '@inertiajs/react';
import type { DjItem, PageProps } from '@/types';

interface DjAvatarCardProps {
    dj: DjItem;
}

export function DjAvatarCard({ dj }: DjAvatarCardProps) {
    const { ziggy } = usePage<PageProps>().props;
    const initials = dj.name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .slice(0, 2)
        .toUpperCase();

    return (
        <Link
            href={route('djs.show', { dj: dj.slug }, false, ziggy)}
            className="flex flex-col items-center gap-2 text-center transition hover:opacity-90"
        >
            <Avatar className="h-16 w-16 border border-border">
                {dj.avatar_url && <AvatarImage src={dj.avatar_url} alt={dj.name} />}
                <AvatarFallback className="font-mono text-sm">{initials}</AvatarFallback>
            </Avatar>
            <span className="text-sm font-medium text-foreground">{dj.name}</span>
        </Link>
    );
}
