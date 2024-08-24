declare namespace App.Data {
export type AccountData = {
id: number | null;
login: string;
password: string;
platform_id: number;
busy: string | null;
email: string | null;
passwordEmail: string | null;
price: number;
status: number | null;
comment: string | null;
user_id: number;
title: string | null;
description: string | null;
};
export type ChatData = {
id: number;
user: App.Data.UserData;
};
export type GameData = {
id: number | null;
name: string;
};
export type MessageData = {
id: number;
message: string | null;
createdAt: string | null;
user: App.Data.UserData;
isOwn: boolean;
};
export type PlatformData = {
id: number | null;
name: string;
};
export type SendMessageData = {
message: string;
};
export type StatisticData = {
id: number | null;
price: number;
account_id: number;
added_at: string;
};
export type UserData = {
id: number | null;
name: string;
email: string;
role_id: number;
password: string;
};
}
