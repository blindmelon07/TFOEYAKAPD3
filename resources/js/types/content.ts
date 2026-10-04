export type SiteSettings = Record<string, string | null>;

export type ChapterStat = {
    id: number;
    value: string;
    label: string;
    sort_order?: number;
};

export type Pillar = {
    id: number;
    icon: string;
    tag: string;
    title: string;
    description: string;
    sort_order?: number;
};

export type Mission = {
    id: number;
    category: string;
    program: string;
    title: string;
    description: string;
    metric_label: string;
    metric_value: string;
    image_path: string | null;
    image_url: string | null;
    image_alt: string | null;
    sort_order?: number;
};

export type FundAllocationColor =
    | 'primary'
    | 'primary-container'
    | 'secondary'
    | 'secondary-container';

export type FundAllocation = {
    id: number;
    label: string;
    percentage: number;
    color: FundAllocationColor;
    sort_order?: number;
};

export type MembershipStep = {
    id: number;
    icon: string;
    title: string;
    description: string;
    requirement: string;
    sort_order?: number;
};

export type SelectOption = { value: string; label: string };

export type ClubOption = { id: number; name: string };

export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
};

export type ReportValueType = 'text' | 'money' | 'number' | 'date' | 'percent';

export type ReportTone = 'good' | 'warning' | 'critical';

export type ReportCell = string | number | null;

export type ReportColumn = {
    key: string;
    label: string;
    type: ReportValueType;
};

export type ReportSection = {
    title: string;
    columns: ReportColumn[];
    rows: Record<string, ReportCell>[];
    totals: Record<string, ReportCell> | null;
    empty: string;
};

export type ReportSummaryItem = {
    label: string;
    value: ReportCell;
    type: ReportValueType;
    tone: ReportTone | null;
};

export type ReportChart = {
    kind: 'columns' | 'proportion';
    title: string;
    valueType: ReportValueType;
    points: { label: string; value: number; tone?: ReportTone }[];
};

export type ReportData = {
    title: string;
    period: string;
    summary: ReportSummaryItem[];
    charts: ReportChart[];
    sections: ReportSection[];
    notice: string | null;
};

export type ReportFilter = {
    name: string;
    label: string;
    type: 'year' | 'date' | 'select';
    options?: SelectOption[];
};

export type ReportCard = {
    key: string;
    name: string;
    description: string;
    icon: string;
};
