export interface Coin {
  id: number;
  title: string;
  status: string;
  subjectshort: string;
  value: number;
  unit: string;
  year: number;
  mintmark: string;
  series: string;
  country: string;
  image?: string | null;
}

export interface CoinDetail {
  id: number;
  title: string;
  obverseImg: string | null;
  reverseImg: string | null;
  status: string;
  region: string;
  country: string;
  period: string;
  ruler: string;
  value: number;
  unit: string;
  type: string;
  series: string;
  subjectshort: string;
  issuedate: string;
  year: number;
  mintage: number;
  material: string;
  mint: string;
  mintmark: string;
  features: string;
  subject: string;
  grade: string;
  paydate: string;
  payprice: number;
  storage: string;
  condition: string;
  quantity: number;
}

export interface Filters {
  status: string[];
  country: string[];
  year: string[];
  series: string[];
  type: string[];
  period: string[];
  mint: string[];
}
