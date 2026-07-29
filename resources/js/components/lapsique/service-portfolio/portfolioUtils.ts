import type {
    ServicePortfolioBundle,
    ServicePortfolioMedia,
    ServicePortfolioProject,
} from '@/types';

function isNewMedia(
    item: ServicePortfolioMedia,
    seenIds: Set<string>,
    seenSources: Set<string>,
): boolean {
    if (seenIds.has(item.id) || seenSources.has(item.src)) {
        return false;
    }

    seenIds.add(item.id);
    seenSources.add(item.src);
    return true;
}

export function getVisiblePortfolioProjects(
    portfolio: ServicePortfolioBundle,
): ServicePortfolioProject[] {
    const seenIds = new Set<string>([portfolio.hero.id]);
    const seenSources = new Set<string>([portfolio.hero.src]);

    return portfolio.projects
        .map((project) => ({
            ...project,
            media: project.media.filter((item) => isNewMedia(item, seenIds, seenSources)),
        }))
        .filter((project) => project.media.length > 0);
}
