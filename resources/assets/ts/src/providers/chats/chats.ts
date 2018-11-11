import {Injectable} from '@angular/core';

import {Api} from '../api/api';
import {Storage} from '@ionic/storage';
import {DatabaseService} from '../../providers/database-service/database-service';
import {UserData} from '../../providers/userdata/userdata';

@Injectable()
export class Chats {

    constructor(private api: Api, private database: DatabaseService, private storage: Storage, private userData: UserData) {}

    sendMessage(message: any) {
        let url = "/messages/send";
        let seq = this.api.post(url, message).share();
        seq.subscribe((data: any) => {
            console.log("Reply send message");
            console.log(JSON.stringify(data));
            let result = data.result;
            message.id = result.id;
            if (result.target_id) {
                message.target_id = result.target_id;
            } else {
                message.target_id = null;
            }
            this.saveMessage(message);
            return message;

            // If the API returned a successful response, mark the user as logged in
        }, err => {
            console.error('ERROR', err);
        });
        return seq;

    }

    deleteMessage(id: any) {
        return new Promise((resolve, reject) => {
            let query = "DELETE FROM messages where id = ?";
            let params = [id];
            this.database.executeSql(query, params)
                .then((res: any) => {
                    console.log("messages deleted");
                    resolve("messages deleted")
                }, (err) => {console.error(err); reject("message not deleted")});
        });
    }
    saveMessage(message: any) {

        let query = "SELECT * FROM messages where id = ? ";
        let params = [message.id];
        this.database.executeSql(query, params)
            .then((res: any) => {
                if (res.rows.length == 0) {
                    let query = "INSERT INTO messages (id, type, name,  message, from_id, to_id,target_id, status, created_at ) VALUES (?,?,?,?,?,?,?,?,?)";
                    let params = [message.id, message.type, message.name, message.message, message.from_id, message.to_id, message.target_id, message.status, message.created_at];
                    this.database.executeSql(query, params)
                        .then((res: any) => {
                            console.log("Location saved");
                        }, (err) => console.error(err));

                }

            }, (err) => console.error(err));
    }
    getChats(trigger: any, typeMarker: any, page: any, per_page: any, target: any) {
        return new Promise((resolve, reject) => {
            let offset = (page - 1) * per_page;
            let user_id = this.userData._user.id;
            console.log("chats sql: " + trigger + " " + typeMarker + " " + user_id);
            if (typeMarker == "user_message") {
                let query = "SELECT * FROM messages where type = 'user_message' and ( (from_id = ? and to_id = ?) or  (from_id = ? and to_id = ?)) order by id desc ";
                query += " limit " + offset + ", " + per_page;
                let params = [trigger, user_id, user_id, trigger];
                console.log(query);
                this.database.executeSql(query, params)
                    .then((res: any) => {
                        let messages = res;
                        console.log("after sql get chat " + typeMarker + " messages results in database" + res.rows.length);
                        resolve(messages);
                    }, (err) => console.error(err));
            } else if (typeMarker == "group_message") {
                let query = "SELECT * FROM messages where type = 'group_message' and  to_id = ? order by id desc ";
                query += " limit " + offset + ", " + per_page;
                let params = [trigger];
                console.log(query);
                this.database.executeSql(query, params)
                    .then((res: any) => {
                        let messages = res;
                        console.log("after sql get chat " + typeMarker + " messages results in database" + res.rows.length);
                        resolve(messages);
                    }, (err) => console.error(err));
            } else if (typeMarker == "group_private_message") {
                let query = "SELECT * FROM messages where type = 'group_message' and  to_id = ? and (target_id = ? OR target_id IN (select contact_id from group_contact where is_admin = 1 and group_id = ?)) order by id desc ";
                query += " limit " + offset + ", " + per_page;
                let params = [trigger, target, trigger];
                console.log(query);
                console.log(trigger);
                console.log(target);
                this.database.executeSql(query, params)
                    .then((res: any) => {
                        let messages = res;
                        console.log("after sql get chat " + typeMarker + " messages results in database" + res.rows.length);
                        resolve(messages);
                    }, (err) => console.error(err));
            } else {
                resolve([]);
            }
        });

    }
    countChats(trigger: any, typeMarker: any, target: any) {
        return new Promise((resolve, reject) => {
            let user_id = this.userData._user.id;
            if (typeMarker == "user_message") {
                let query = "SELECT COUNT(id) as total FROM messages where type = 'user_message' and ( (from_id = ? and to_id = ?) or  (from_id = ? and to_id = ?))";
                console.log(query);
                let params = [trigger, user_id, user_id, trigger];
                this.database.executeSql(query, params)
                    .then((res: any) => {
                        console.log("after sql get chat messages: results in database" + res[0].total);
                        if (res.rows.length > 0) {
                            resolve(res[0].total);
                        } else {
                            resolve(0);
                        }
                    }, (err) => console.error(err));
            } else if (typeMarker == "group_message") {
                let query = "SELECT COUNT(id) as total FROM messages where type = 'group_message' and  to_id = ? ";
                console.log(query);
                let params = [trigger];
                this.database.executeSql(query, params)
                    .then((res: any) => {
                        console.log("after sql get chat messages: results in database" + res[0].total);
                        if (res.rows.length > 0) {
                            resolve(res[0].total);
                        } else {
                            resolve(0);
                        }
                    }, (err) => console.error(err));
            } else if (typeMarker == "group_private_message") {
                let query = "SELECT COUNT(id) as total FROM messages where type = 'group_message' and  to_id = ? and (target_id = ? OR target_id IN (select contact_id from group_contact where is_admin = 1 and group_id = ?))";
                console.log(query);
                let params = [trigger, target];
                this.database.executeSql(query, params)
                    .then((res: any) => {
                        console.log("after sql get chat messages: results in database" + res[0].total);
                        if (res.rows.length > 0) {
                            resolve(res[0].total);
                        } else {
                            resolve(0);
                        }
                    }, (err) => console.error(err));
            }
        });

    }


    getLastLocationId(): Promise<string> {
        return this.storage.get('lastLocationId').then((value) => {
            return value;
        });
    }
    /**
     * Saves username in local storage.
     */
    setLastLocationId(locationId: string): Promise<any> {
        return this.storage.set('lastLocationId', locationId);
    }

}
